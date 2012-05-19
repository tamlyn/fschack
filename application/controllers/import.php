<?php

require_once 'lib/Excel/reader.php';

$data = new Spreadsheet_Excel_Reader();
// $data->read('C:\www\fschack\application\data\09-3 River Harbourne March 2009.xls');
$sheet = $data->sheets[0];
//print_r($sheet['cells']);

print_r($sheet);

$sites = array();
for ($col = 2; $col <= 13; $col++) {
	if (isset($sheet['cells'][4][$col])) {
		$sites[$col] = (object)array('name'=>$sheet['cells'][4][$col], 'data'=>array());
	}
}

$rows = array(
	'water_width'=>5,
	'wetted_perimeter'=>6,
	'gradient'=>7,
	'depth'=>range(8,12),
	'flowrate'=>range(14,16),
	'bedload_length'=>range(24, 33),
	'roundness'=>range(35,40)
);
$failures = array();
foreach ($rows as $type=>$rows) {
	if (!is_array($rows)) {
		$rows = array($rows);
	}
	foreach ($sites as $col=>$siteData) {
		$siteData->data[$type] = array();
		foreach ($rows as $row) {
			if (isset($sheet['cells'][$row][$col])) {
				$siteData->data[$type][] = $sheet['cells'][$row][$col];
//				echo "<td>$row, $col: \"".$sheet['cells'][$row][$col]."\"</td>";
			} else {
				$siteData->data[$type][] = '?';
//				$failures[] = array($row, $col);
			}
		}
	}
}

?>


<ul>
<?php foreach ($sites as $site) : ?>
	<li>
		<?php echo $site->name; ?>
		<ul>
			<?php foreach ($site->data as $type=>$values) : ?>
			<li>
				<?php echo $type; ?>: <?php echo implode(', ', $values); ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</li>
<?php endforeach; ?>
</ul>
