<?php
	$function=$_REQUEST['function'];
	$function();

	function search()
	{
		$request = $_REQUEST['request'];
		$search_type = $_REQUEST['searchType'];
		$sort_type = $_REQUEST['sortType'];

		// Criteria #1: Use Rest Countries API as data source
		switch ($search_type) {
			case 'name':
				$response = 'https://restcountries.eu/rest/v2/name/' . $request;
				break;
			case 'full':
				$response = 'https://restcountries.eu/rest/v2/name/' . $request . '?fullText=true';
				break;
			case 'code':
				$response = 'https://restcountries.eu/rest/v2/alpha/' . $request;
				break;
			default:
				$response = 'https://restcountries.eu/rest/v2/all';
				break;
		}

		// Criteria #3: Error message displayed if users subnit form without input or if search yields no results
		if (!$response = @file_get_contents($response)) {
			echo '<h3>The input submitted yielded no results.</h3>';
			return;
		}
		// Criteria #8: Using php create API endpoint that will request data from Rest Countries. Should return JSON and include all data necessary
		$response = ($search_type == 'code') ? '[' . $response . ']' : $response; // alpha lookup does not return an array, must convert before JSON decode
		$response = json_decode($response);

		/////////////////
		// Echo Table //
		///////////////
		// Echo table headers
		echo '<table><tr><th>Full Name</th>' .
			'<th>Alpha Code 2</th>' .
			'<th>Alpha Code 3</th>' .
			'<th>Flag Image</th>' .
			'<th>Region</th>' .
			'<th>Subregion</th>' .
			'<th>Population</th>' .
			'<th>Language(s) Spoken</th></tr>';

		// Sort response by name or population
		if ($sort_type == 'name')
			usort($response, function($a, $b) {
				return strcmp($a->name, $b->name);
			});
		else
			usort($response, function($a, $b) {
				return ($b->population - $a->population);
			});

		// Criteria #5: Sort results alphabetically and limit API results to 50
		$total = count($response);
		$count = ($total > 50 && $search_type != 'all') ? 50 : $total;

		// Criteria #6: For each country displayed include full name, alpha code 2, ...
		for ($i = 0; $i < $count; $i++)	{
			echo '<tr><td>' . $response[$i]->name . '</td>' .
				'<td>' . $response[$i]->alpha2Code . '</td>' .
				'<td>' . $response[$i]->alpha3Code . '</td>' .
				'<td><img src="' . $response[$i]->flag . '"</td>' .
				'<td>' . $response[$i]->region . '</td>' .
				'<td>' . $response[$i]->subregion . '</td>' .
				'<td>' . number_format($response[$i]->population) . '</td><td>';
			// List language(s)
			for ($j = 0; $j < count($response[$i]->languages); $j++) {
				if ($j > 0)
					echo '<br>';
				echo $response[$i]->languages[$j]->name;
			}
			echo '</td></tr>';
		}
		echo '</table>';

		///////////////////////////////////////////////
		// Echo # of Countries, Regions, Subregions //
		/////////////////////////////////////////////
		// Criteria #7: At bottom of page show total number of countries and list all regions & subregions
		echo '<h3>Total number of countries in search: ' . $total . '</h3>';
		if ($total > 50 && $search_type != 'all')
			echo '<p>Limiting display to first 50 results in alphabetical order:</p>';

		// Span subregions across 4 columns to allow a more condensed list
		echo '<table><tr><th>Regions</th><th colspan="4">Subregions</th></tr><tr><td>';

		// Create arrays for regions & subregions
		$result_regions = $result_subregions = array();
		foreach ($response as &$value) {
			array_push($result_regions, $value->region);
			array_push($result_subregions, $value->subregion);
		}

		// Echo amount of countries in each region
		$region_totals = array_count_values($result_regions);
		foreach ($region_totals as $key => $value)
			if ($key)
				echo $key . ': ' . $value . '<br>';

		echo '</td><td>';

		// Echo amount of countries in each subregion
		$subregion_totals = array_count_values($result_subregions);
		$ccount = 1;
		foreach ($subregion_totals as $key => $value) {
			if ($key) {
				// Break into separate columns for easier readibility
				if ($ccount++ % 6 == 0)
					echo '</td><td>';
				echo $key . ': ' . $value . '<br>';
			}
		}

		echo '</td></tr></table>';
	}
?>