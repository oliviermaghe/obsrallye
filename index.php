<?php
if (@$_REQUEST["ajax"] == "write") {
	$myfile = fopen($_REQUEST["scope"] . ".txt", "w");
	$txt = $_REQUEST["contenu"];
	fwrite($myfile, $txt);
	fclose($myfile);
	die ("done");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>OBS Import</title>
	<style>
		body {
			font-size: 20pt;
			text-align: center;
		}

		h1,
		h2 {
			margin: 0
		}

		div.container {
			max-width: 1000px;
			margin-inline: auto;

			& div {
				text-align: center;
			}
		}

		input {
			font-size: 10pt;
			width: 400px;
		}

		textarea {
			width: 20em;
			height: 30em;
		}

		.flex {
			display: flex;
		}
	</style>
</head>

<body>
	<div class="container">
		<h1>OBS Rally Overall Import</h1>
		<p>Chrome CORS extention is needed</p>
		<div class="flex">
			<!-- I need 2 overall in my case, special and general -->
			<div>
				<h2>Special</h2>
				<input type="text" placeholder="File url" data-filename="speciale" value="raw.txt" />
				<br />
				<!-- https://www.liverally.be:8443/screen/service/scrtxtTable/ss5-r1.txt -->
				<textarea name="textbox"></textarea>
				<br />
				<button onclick="generate(this);">Create
					file</button>
			</div>
			<div>
				<h2>General</h2>
				<input type="text" placeholder="File url" data-filename="general"
					value="https://www.liverally.be:8443/screen/service/scrtxtTable/ge5-r1.txt" />
				<br />
				<textarea name="textbox"></textarea>
				<br />
				<button onclick="generate(this);">Create
					file</button>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		function generate(obj) {
			const input = obj.parentNode.querySelector("input");
			const url = input.value;
			const filename = input.dataset.filename;
			const textbox = obj.parentNode.querySelector("textarea");

			fetch(url)
				.then(function (response) {
					if (response.ok) {
						return response.text();
					}
					throw response;
				})
				.then(function (text) {
					let contenu = [];

					// this will be used to align content, like in a table
					let positionLength = 0;
					let piloteLength = 0;
					let tempsLength = 0;

					// choose the end of line depending of your source file
					returnCar = "\r\n";
					returnCar = "\n";

					// loop through source file
					for (line of text.split(returnCar)) {
						// this is my personnal use...
						// pilote position
						firstWhite = line.indexOf(" ")
						positionPilote = line.substring(0, firstWhite)

						// pilote timing
						let startTemps = -1;
						for (let i = line.length - 1; i >= 0; i--) {
							if (/ /.test(line[i])) {
								startTemps = i;
								break;
							}
						}
						tempsPilote = line.slice((line.length - startTemps - 1) * -1);

						// pilot name start and end
						startPilote = line.slice(firstWhite).search(/[a-zA-Z]/) + 1;

						let endPilote = -1;
						for (let i = startTemps; i >= startPilote; i--) {
							if (/[a-zA-Z]/.test(line[i])) {
								endPilote = i;
								break;
							}
						}
						nomPilote = line.substring(startPilote, endPilote + 1).trim();

						// if this position lenght is the longest one? The pilote name? The pilote timing?
						if (positionPilote > -1) {
							if (nomPilote.length > piloteLength) piloteLength = nomPilote.length;
							if (tempsPilote.length > tempsLength) tempsLength = tempsPilote.length;
							if (positionPilote.length > positionLength) positionLength = positionPilote.length;
						}

						contenu.push([positionPilote, nomPilote, tempsPilote]);
					}
					// debug
					// console.table(contenu);
					// console.log("longueur position", positionLength);
					// console.log("longueur pilote", piloteLength);
					// console.log("longueur temps", tempsLength);

					// build the text output, aligned like a table
					// fill the column with blank car untill it is = to longuest 
					let textBoxcontent = "";
					for (line of contenu) {
						textBoxcontent += (line[0] + " ".repeat(positionLength)).substr(0, positionLength) + " ";
						textBoxcontent += (line[1] + " ".repeat(piloteLength)).substr(0, piloteLength) + " ";
						textBoxcontent += line[2].trim() + "\r\n";
					}
					textBoxcontent += "-".repeat(positionLength + piloteLength + tempsLength + 1) + "\r\n \r\n \r\n";

					// output to user only
					textbox.value = textBoxcontent;

					// send to write page, this will write
					postAjax('?ajax=write', { contenu: textBoxcontent, scope: filename }, function (data) {
						if (data == "done") textbox.value += "\nDone: " + filename;
					});
				});
		}

		function postAjax(url, data, success) {
			var params = typeof data == 'string' ? data : Object.keys(data).map(
				function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]) }
			).join('&');

			var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
			xhr.open('POST', url);
			xhr.onreadystatechange = function () {
				if (xhr.readyState > 3 && xhr.status == 200) { success(xhr.responseText); }
			};
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xhr.send(params);
			return xhr;
		}

	</script>
</body>

</html>