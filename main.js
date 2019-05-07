function searchRestCountries() {
	const input = document.getElementById('input').value;
	const linput = input.length;

	// Start getString
	let getString = 'getRestCountries.php?function=search&request=' + input;
	// Determine Search Type
	if (document.getElementById('rNameCode').checked)
		getString += (linput == 2 || linput == 3) ? '&searchType=code' : '&searchType=name';
	else if (document.getElementById('rFull').checked)
		getString += '&searchType=full';
	else
		getString += '&searchType=all';
	// Determine Sort Type
	getString += (document.getElementById('rPopSort').checked) ? '&sortType=pop' : '&sortType=name';

	// Criteria #4: Form data submitted via JS & displayed without page reloading
	const xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function () {
		document.getElementById('result').innerHTML = (this.readyState === this.DONE) ? xhr.responseText : '<h3>Generating Results...</h3>';
	}
	xhr.open('GET', getString);
	xhr.send();
}