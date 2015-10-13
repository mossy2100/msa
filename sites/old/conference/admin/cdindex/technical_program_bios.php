<?php 
$sessions = get_sessions("StartTime");
$pageNumberOf = array_flip(get_paper_pages( ));

$days = array();
foreach ($sessions as $session)
{
	$date = format_date("Y-m-d", $session -> StartTime);
	if (!$days[$date])
		$days[$date] = array();
	$days[$date][] = get_session_slots_info($session -> SessionID);
}

?>
<html>
<head>
<title>
Technical Program
</title>
<style type="text/css">
  div { padding-top: 3 ; padding-bottom: 3 }
  .PageTitle { text-align: center ; font: bolder xx-large "Comic Sans MS" ; color: teal}
  .Day { font-style: italic }
  .Session { font-weight: bold ; font-family: "Comic Sans MS" }
  .Indent {float:left; width:2%}
  .LeftColumn {float:left; width:78%;}
  .RightColumn {float:left; width:20%; text-align: right;}
  .Info{ font-weight: normal ; font-family: "Comic Sans MS" }
  .Abstract { font-style: italic ; font-size: smaller }
  .Bio { font-size: smaller }
  .PaperName {}
  .Authors {}
  .PageNumber {}
 </style>
</head>
<body>

<div class="PageTitle">
Technical Program
</div>

<?php
ksort($days);
foreach ($days as $date => $sessions)
{
	?>
<div class="Day">
<?php echo format_date("l jS F",$date) ?>
</div>
	<?php
	foreach ($sessions as $session)
	{
		?>
		<div class="Session">
		<?php echo $session -> Session -> SessionName; ?>
		<span class="Info">
		<?php echo "&nbsp;&nbsp;"; ?>
		<?php echo format_date("g:ia",$session -> Session -> StartTime); ?>
		<?php echo " to "; ?>
		<?php echo format_date("g:ia",$session -> Session -> EndTime); ?>
		</span>
		<span class="Info">
		<?php echo "&nbsp;&nbsp;Venue: "; ?>
		<?php echo $session -> Session -> RoomName; ?>
		</span>
		<span class="Info">
		<?php echo "&nbsp;&nbsp;Chair: "; ?>
		<?php echo $session -> Session -> FirstName." ".$session -> Session -> LastName; ?>
		</span>
		</div>
	<?php
		foreach ($session -> Slots as $slot)
		// Use 3 column layout to provide indent to prevent
		// Mozilla breaking on padding-left. Also
		// need <br clear=all> to get columns in Mozilla
		{
			$paper = get_paper_info($slot -> PaperID);
			$firstPage = $pageNumberOf[$slot -> PaperID];
			$authors = retrieve_authors($paper -> PaperID);
			$fileInfo = get_latestFile($paper -> PaperID , &$err_message );
			$fileEnding = strstr($fileInfo -> FileName, '.');
			?>
			<div class="Indent"><nbsp;></div>  
			<div class="LeftColumn">
				<span class="Slot.PaperName">
				<a href="/papers/<?php echo $paper -> PaperID . $fileEnding ?>">
				<?php echo $paper -> Title ?>
				</a>
				</span>
				<br />
				<span class="Authors">
				<?php echo $authors ?>
				</span>
				<br /><br />
				<span class="Abstract">
				<?php echo $paper->PaperAbstract ?>
				</span>
				<br /><br />
				<span class="Bio">
				<?php echo $paper->PresenterBio ?>
				</span>
			</div>
	<!--
			<div class="RightColumn">
				<span class="PageNumber">
				<?php echo $firstPage ?>
				</span>
			</div>
	-->
			<br clear=all>

		<?php
			}
		?>
		<?php
	}
}
?>
</body>
</html>
