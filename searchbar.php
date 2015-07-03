<div id="searchbar-div">
	<form id="searchbar-form" action="search.php" method="get">
		<div id="input_container">
			<input class="search" type="text" name="query" placeholder="Search.. try things like 'francisco' or 'phone'" value="<?php echo $query; ?>" required>
			<img src="images/filter.png" id="input_img">
			<img src="images/filtered.png" id="filtered_search" style="display: none;">
			<input class="blue-button" type="submit" value="Search">
		</div>
		<ul class="search-dropdown" style="display: none;">
			<li class="search-dropdown-header">Filter your search</li>
			<li><input type="checkbox" id="available" onclick="checkChanged()" name="available" value="true" <?php if ($available === 'true') echo "checked"; ?> /><label for="Available">Available Devices</label></li>
			<li><input type="checkbox" id="dev" onclick="checkChanged()" name="dev" value="true" <?php if ($dev === 'true') echo "checked"; ?> /><label for="DevProvisioned" />Dev Provisioned (iOS)</label></li>
		</ul>
	</form>
</div>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript">
	$(function() {
		$("#input_img").bind('focus click', function() {
			$(".search-dropdown").show();
			$("#input_img").css('opacity', '0.5');
		});
		
		$("#searchbar-form").bind('mouseleave', function() {
			$(".search-dropdown").hide();
			$("#input_img").css('opacity', '1.0');
		});

		var isFiltered = "<?= ($available === 'true' || $dev === 'true')?'true':'false'; ?>"
		if (isFiltered == "true") {
			$("#filtered_search").show();
		}
	});
	
	function checkChanged() {
		var availableFilter = document.getElementById('available');
		var devFilter = document.getElementById('dev');
		if (availableFilter.checked || devFilter.checked) {
			$("#filtered_search").show();
		}
		else {
			$("#filtered_search").hide();
		}
	}
</script>