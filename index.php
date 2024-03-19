<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>OBS Classement</title>

	<script src="https://code.jquery.com/jquery-3.6.4.min.js"
		integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>
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
			height: 26em;
		}

		.flex {
			display: flex;
		}
	</style>
</head>

<!-- 1   ROUARD             25:45.1
2   VERSTAPPEN        +00:11.3
3   POTTY             +00:36.1
4   HODENIUS          +01:16.4
5   MAZUIN            +01:57.4
6   SERDERIDIS        +02:04.5
7   BEDORET           +02:24.0
8   STAMPAERT         +02:25.1
9   DECOCK            +02:47.5
10  DEWILDE           +03:08.7
11  SEPTON J�r�me     +03:44.9
12  PEX               +04:04.4
13  SCHLOESSER        +04:15.8
14  DETHISE Jonathan   +04:22.9
15  SCHMITT           +04:45.8
16  GROPP             +05:59.9
17  REVILLOD          +06:15.0
18  SPITTAELS         +06:36.6
19  FIASSE            +06:39.2
20  BEMMANN           +06:44.0
	-->

<body>
	<div class="container">
		<h1>OBS Rallye</h1>
		<p>Activer le CORS extention dans Chrome</p>
		<div class="flex">
			<div>
				<h2>Spéciale</h2>
				<input type="text" id="urlspeciale"
					value="https://www.liverally.be:8443/screen/service/scrtxtTable/ss5-r1.txt" />
				<br />
				<textarea id="contenuspeciale" name="textbox"></textarea>
				<br />
				<button onclick="generate('speciale'); return false;">Create
					file</button>
			</div>
			<div>
				<h2>Général</h2>
				<input type="text" id="urlclassement"
					value="https://www.liverally.be:8443/screen/service/scrtxtTable/ge5-r1.txt" />
				<br />
				<textarea id="contenuclassement" name="textbox"></textarea>
				<br />
				<button onclick="generate('general');return false;">Create
					file</button>
			</div>
		</div>
	</div>

	<script>
		(function () {
			var textFile = null,
				makeTextFile = function (text) {
					var data = new Blob([text], { type: "text/plain" });

					// If we are replacing a previously generated file we need to
					// manually revoke the object URL to avoid memory leaks.
					if (textFile !== null) {
						window.URL.revokeObjectURL(textFile);
					}

					textFile = window.URL.createObjectURL(data);

					return textFile;
				};
		})();

		function generate(scope) {
			let textbox = "";
			let url = "";
			let filename = "";
			switch (scope) {
				case "speciale":
					url = document.getElementById("urlspeciale").value;
					filename = "speciale";
					textbox = "contenuspeciale";
					break;
				case "general":
					url = document.getElementById("urlclassement").value;
					filename = "classement";
					textbox = "contenuclassement";
					break;
				default: return false;
			}

			if (url === "") return false;
			if (textbox === "") return false;
			if (filename === "") return false;

			fetch(url)
				.then(function (response) {
					if (response.ok) {
						return response.text();
					}
					throw response;
				})
				.then(function (text) {
					let contenu = [];
					let positionLength = 0;
					let piloteLength = 0;
					let tempsLength = 0;
					for (line of text.split("\r\n")) {
						// position pilote
						firstWhite = line.indexOf(" ")
						positionPilote = line.substring(0, firstWhite)

						let startTemps = -1;
						for (let i = line.length - 1; i >= 0; i--) {
							if (/ /.test(line[i])) {
								startTemps = i;
								break;
							}
						}
						tempsPilote = line.slice((line.length - startTemps - 1) * -1);

						// nom pilote
						startPilote = line.slice(firstWhite).search(/[a-zA-Z]/) + 1;

						let endPilote = -1;
						for (let i = startTemps; i >= startPilote; i--) {
							if (/[a-zA-Z]/.test(line[i])) {
								endPilote = i;
								break;
							}
						}
						nomPilote = line.substring(startPilote, endPilote + 1).trim();
						if (positionPilote > -1) {
							if (nomPilote.length > piloteLength) piloteLength = nomPilote.length;
							if (tempsPilote.length > tempsLength) tempsLength = tempsPilote.length;
							if (positionPilote.length > positionLength) positionLength = positionPilote.length;
						}
						contenu.push([positionPilote, nomPilote, tempsPilote]);
					}

					console.log("longueur max", piloteLength);
					// fabrication de text
					let textBoxcontent = "";
					let chaineVide = "_";
					for (line of contenu) {
						textBoxcontent += (line[0] + chaineVide.repeat(positionPilote)).slice(positionPilote) + line[1] + chaineVide.repeat(piloteLength) + line[2] + chaineVide.repeat(tempsLength) + "\r";
					}


					document.getElementById(textbox).value = textBoxcontent;
					//document.getElementById(textbox).value = text;
					// postAjax('write.php', { contenu: contenu, scope: filename }, function (data) {
					// 	if (data == "done") document.getElementById(textbox).value = document.getElementById(textbox).value + "\nDone: " + filename;
					// });
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