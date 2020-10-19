<?php

# Скрипт редактирования резюме в браузере
# Оригинал храниться в index.html

readfile("index.html");

if (!in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '188.155.128.100'))) exit;
if (isset($_SERVER["CONTENT_LENGTH"])) file_put_contents('index.html', file_get_contents('php://input'));

?>

<script>
	function editable(state) {
		Array.prototype.slice.call(document.querySelectorAll('nav, header, dd, span')).forEach(function(query) {
			state ? query.setAttribute('contenteditable', true) : query.removeAttribute('contenteditable');
			state ? query.setAttribute('spellcheck', false) : query.removeAttribute('spellcheck');
		});
	}

	window.addEventListener('load', function() {
		var style = document.createElement('style');

		style.appendChild(document.createTextNode('[contenteditable=true]:focus {display: block; outline: none; background: rgba(243,156,18,.1)}'));
		style.appendChild(document.createTextNode('aside {position: fixed; top: 10px; left: 10px; opacity: .9; text-align: center; font-size: 12px; text-shadow: 0 0 0 #fff; cursor: pointer; user-select: none}'));
		style.appendChild(document.createTextNode('mark {position: absolute; width: 24px; height: 24px; border-radius: 12px; color: transparent; transition: background-color .2s linear; display: none}'));
		style.appendChild(document.createTextNode('@media only screen and (max-width: 1024px) { aside {display: none} }'));

		var widget = document.createElement('aside');

		var buttons = [
			['edit', '&#10033;', '#505050; display:block', '#646464'],
			['confirm', '&#10004;', '#27ae60', '#2cc36b'],
			['cancel', '&#10006;', '#e74c3c; left: 30px', '#ea6153'],
			['busy', '&#8766;', '#2980b9', '#2e8ece']
		];

		buttons.forEach(function(item, i) {
			var mark = document.createElement('mark');

			mark.className = item[0];
			mark.innerHTML = item[1];

			style.appendChild(document.createTextNode('.' + item[0] + ' {background: ' + item[2] + '; box-shadow: 0 0 25px 0 ' + item[3] + '}'));
			style.appendChild(document.createTextNode('.' + item[0] + ':hover {background: ' + item[3] + '}'));

			widget.appendChild(mark);
		});

		document.body.appendChild(widget);
		document.getElementsByTagName('head')[0].appendChild(style);
	});

	var backup;

	window.addEventListener('click', function() {
		switch (event.target.className) {
			case 'edit':
				backup = document.body.innerHTML;
				document.getElementsByClassName('edit')[0].style.display = 'none';
				document.getElementsByClassName('confirm')[0].style.display = 'block';
				document.getElementsByClassName('cancel')[0].style.display = 'block';
				editable(true);
			break;

			case 'confirm':
				document.getElementsByClassName('confirm')[0].style.display = 'none';
				document.getElementsByClassName('cancel')[0].style.display = 'none';
				document.getElementsByClassName('busy')[0].style.display = 'block';
				document.getElementsByClassName('busy')[0].animate([{opacity: .9}, {opacity: .5}, {opacity: .9}], {duration: 900, iterations: Infinity});
				editable(false);

				var data = document.cloneNode(true);

				data.getElementsByTagName('style')[1].remove();
				data.getElementsByTagName('aside')[0].remove();
				data.getElementsByTagName('script')[0].remove();

				data.body.innerHTML = data.body.innerHTML.trimRight();
				data.body.innerHTML = data.body.innerHTML.replace('</main>', '</main>\n');

				http = new XMLHttpRequest();

				http.open('POST', '/');
				http.send(data);

				http.onreadystatechange = function()
				{
					if (http.readyState == 4 && http.status == 200)
					{
						document.getElementsByClassName('edit')[0].style.display = 'block';
						document.getElementsByClassName('busy')[0].style.display = 'none';
					}
				}
			break;

			case 'cancel':
				document.body.innerHTML = backup;
				document.getElementsByClassName('edit')[0].style.display = 'block';
				document.getElementsByClassName('confirm')[0].style.display = 'none';
				document.getElementsByClassName('cancel')[0].style.display = 'none';
				document.getElementsByClassName('busy')[0].style.display = 'none';
				editable(false);
			break;
		}
	}, true);
</script>