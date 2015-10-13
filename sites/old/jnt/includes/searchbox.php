<form action="datasheet.php" method="post">
<?php
$SQL="SELECT * FROM records";
echo "<SELECT NAME='site'>";
	$result=mysql_query($SQL);
	while($data=mysql_fetch_assoc($result))
		{  ?>
 <OPTION VALUE="<?php echo $data['place']; ?>"><?php echo $data['place']; ?>
 </OPTION>"
       <?php }?>

<input type="submit" value="View Entry">
</select>
</form>