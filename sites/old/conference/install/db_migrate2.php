<?php

//
// SessionTrack enhancement Dec 7th 2006
//

$php_root_path = ".." ;
require_once("$php_root_path/includes/include_all_fns.inc");
require_once("$php_root_path/includes/page_includes/page_fns.php");

$db = adodb_connect();

if (!$db)
    return "Could not connect to database server - please try later.";

$result = $db -> Execute("SELECT COUNT(*) FROM " . $GLOBALS["DB_PREFIX"] . "SessionTrack");
if ($result) {
    echo "database has already been migrated. SessionTrack enhancement may now be used.";
    return;
}

$sql = <<<EOT
CREATE TABLE SessionTrack /* SessionTrack enhancement Dec 7th 2006 */
(
        SessionTrackID TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY , /* SessionTrack ID of this submitted paper */
        SessionTrackName VARCHAR(100) NOT NULL /* Medical 01 / Physics 02 / Electrical 03 / etc... */
);
EOT;
$result = $db -> Execute( $sql );
if ($result)
    echo "Table (SessionTrack) creation succeeded<br>\n";

$sql = 'ALTER TABLE Paper ADD COLUMN SessionTrackID INT UNSIGNED DEFAULT NULL;';
$result = $db -> Execute( $sql );
if ($result)
    echo "Table (Paper) alteration succeeded<br>\n";

$sql = 'ALTER TABLE Session ADD COLUMN SessionTrackID INT UNSIGNED DEFAULT NULL;';
$result = $db -> Execute( $sql );
if ($result)
    echo "Table (Session) alteration succeeded<br>\n";

echo 'DONE.';
?>


