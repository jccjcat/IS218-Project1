<!DOCTYPE html>
<!--
  Jan Chris Tacbianan
  IS218-102 - Spring 2015
  Project 1
-->
<html>
<head>
    <title>
        University and College Directory
    </title>
<style>
table, th, td {
    border: 1px solid black;
}
</style>
</head>
<body>
<?php
/*
 * CSV Reader Class
 * -Singleton Class used for extracting data from the CSV Data File
 * -Singleton Class used for extracting data from the CSV Data File
 */

class CSVReader {

    /**
     * Get Instance Function
     * Method used to retrieve the CSV Reader Object.
     */
    public static function getInstance() {
        static $instance = null; //Define Instance
        if (null === $instance) { //If Instance does not exist
            $instance = new CSVReader();//Create it
        }
        return $instance; //Return Instance
    }
    
    /**
     * Read Data Function.
     * Reads in the data from the CSV File.
     * @return Return an array where the UNITID is the key and the value is an additional array.
     */
    public function readData() {
        $location = "data.csv";//Pre-define Locations
        if (func_num_args() != 0) { //Check if there is any additional arguments
            $location = func_get_arg(0);
        }
        $firstLine = false;
        $headers = array();
        $result = array();
        if (($inputFile = fopen($location, 'r')) !== FALSE) {//Open File
            while (($data = fgetcsv($inputFile, 0, ",")) !== FALSE) {//Iterate over the entire file and getting the values as an array.
                if (!$firstLine) {
                    $headers = $data;
                    $firstLine = true;
                } else {
                    $record = array_combine($headers, $data);
                    $result[$record['UNITID']] = $record;
                    //print_r($result); Diagnostic Line
                    //echo '<br>';
                }
            }
            fclose($inputFile); //Close the file.
        } else {
            echo "Error opening file!";
        }
        return $result; //Return an array where the UNITID is the key and the value is an additional array.
    }

}

/**
 * Record Creation Factory 
 * A Factory for creating formatted records
 */
class RecordFactory {
    /**
     * 
     * @param type $recordValues
     * @return A new record object with the values (an array) passed in as an argument
     */
    public static function createFactory($recordValues) {
        return new Record($recordValues);
    }

}

/**
 * Record Class
 * -A class used to store a college's record data.
 */
class Record {

    private $data; //The raw data as an array of values with the headers as keys.
    private $link; //A string containing the HTML line for a link to the record.
    private $entry; //A string containing the HTML line(s) for the actual record.

    /**
     * Constructor
     * Constructs the record
     * @param array $data An array that should contain the values of record.
     */
    public function __construct($data) {
        $this->data = $data;
        $this->link = "<a href=\"" . $_SERVER['PHP_SELF'] . "?entry=" . $data["UNITID"] . "\">" . $data["INSTNM"] . "</a>";
        $this->createEntry(); //Creating $entry from this function call
    }

    /**
     * Create Entry
     * Create the HTML code for the record table.
     */
    private function createEntry() {
        $this->entry = "<table>\n<tr>\n";//Set up table and first row.
        foreach (Constants::$headerNames as $value) {
            $this->entry .= "<td>" . Constants::$friendlyNames[$value] . "</td>\n"; //Input the values for the Headers
        }
        $this->entry .="</tr>\n<tr>\n";
        foreach (Constants::$headerNames as $value) {//Input the actual values for the second row. 
            $this->entry .= "<td>" . $this->data[$value] . "</td>\n";
        }
        $this->entry .= "</tr>\n</table>\n";
    }
    
    /**
     * Get Link
     * Accessor method for the link string
     * @return string The HTML link string.
     */
    public function getLink() {
        return $this->link;
    }
    
    /**
     * Get Entry
     * Accessor method for the entry string
     * @return string The HTML table for the record.
     */
    public function getEntry() {
        return $this->entry;
    }

}

/**
 * Page Class
 * Class responsible for the constructor of the actual web page.
 */
class Page {

    private $records; //The array containing all the values in different arrays
    private $recordContent; //An array containing the constructed record objects.

    /**
     * Constructor
     * Constructs the values of the page.
     */
    public function __construct() {
        $reader = CSVReader::getInstance();
        $this->records = $reader->readData();
        $this->recordContent = array();
        $this->format();
    }

    /**
     * Format Method
     * Creates a record object for each set of values
     */
    public function format() {
        foreach ($this->records as $key => $value) {
            $this->recordContent[$key] = RecordFactory::createFactory($value);
        }
    }

	/**
	 * Destructor
	 * Outputs the page onces the page object is disposed of.
	 */
    public function __destruct() {  
        if (!(isset($_GET["entry"]))) {
            foreach($this->recordContent as $record) {
                echo $record->getLink();
                echo "<br>\n";
            }
        } else {
            $id = $_GET["entry"];
            $record = $this->recordContent[$id];
            echo $record->getEntry();
            echo "<br>\n";
        }
    }

}
/**
 * Constants Class
 * Contains constant objects that are used universally.
 */
class Constants {
	/**
	 * Friendly Names - an array containing the full header names.
	 */
    public static $friendlyNames = array(
        "UNITID" => "Unique Identification Number", //UNITID
        "INSTNM" => "Institution (entity) name", //INSTNM
        "ADDR" => "Street address or post office box", //ADDR
        "CITY" => "City location of institution", //CITY
        "STABBR" => "State Abbreviation", //STABBR
        "ZIP" => "ZIP code", //ZIP
        "FIPS" => "FIPS state code", //FIPS
        "OBEREG" => "Geographic region", //OBEREG
        "CHFNM" => "Name of chief administrator", //CHFNM
        "CHFTITLE" => "Title of chief administrator", //CHFTITLE
        "GENTELE" => "General information telephone number", //GENTELE
        "FAXTELE" => "Fax number", //FAXTELE
        "EIN" => "Employer Identification Number", //EIN
        "OPEID" => "Office of Postsecondary Education (OPE) ID Number", //OPEID
        "OPEFLAG" => "OPE Title IV eligibility indicator code", //OPEFLAG
        "WEBADDR" => "Institution's internet website address", //WEBADDR
        "ADMINURL" => "Admissions office web address", //ADMINURL
        "FAIDURL" => "Financial aid office web address", //FAIDURL
        "APPLURL" => "Online application web address", //APPLURL
        "NPRICURL" => "Net price calculator web address", //NPRICURL
        "SECTOR" => "Sector of institution", //SECTOR
        "ICLEVEL" => "Level of institution", //ICLEVEL
        "CONTROL" => "Control of institution", //CONTROL
        "HLOFFER" => "Highest level of offering", //HLOFFER
        "UGOFFER" => "Undergraduate offering", //UGOFFER
        "GROFFER" => "Graduate offering", //GROFFER
        "HDEGOFR1" => "Highest degree offered", //HDEGOFR1
        "DEGGRANT" => "Degree-granting status", //DEGGRANT
        "HBCU" => "Historically Black College or University", //HBCU
        "HOSPITAL" => "Institution has hospital", //HOSPITAL
        "MEDICAL" => "Institution grants a medical degree", //MEDICAL
        "TRIBAL" => "Tribal college", //TRIBAL
        "LOCALE" => "Degree of urbanization (Urban-centric locale)", //LOCALE
        "OPENPUBL" => "Institution open to the general public", //OPENPUBL
        "ACT" => "Status of institution", //ACT
        "NEWID" => "UNITID for merged schools", //NEWID
        "DEATHYR" => "Year institution was deleted from IPEDS", //DEATHYR
        "CLOSEDAT" => "Date institution closed", //CLOSEDAT
        "CYACTIVE" => "Institution is active in current year", //CYACTIVE
        "POSTSEC" => "Primarily postsecondary indicator", //POSTSEC
        "PSEFLAG" => "Postsecondary institution indicator", //PSEFLAG
        "PSET4FLG" => "Postsecondary and Title IV institution indicator", //PSET4FLG
        "RPTMTH" => "Reporting method for student charges, graduation rates, retention rates and student financial aid", //RPTMTH
        "IALIAS" => "Institution name alias", //IALIAS
        "INSTCAT" => "Institutional category", //INSTCAT
        "CCBASIC" => "Carnegie Classification 2010: Basic", //CCBASIC
        "CCIPUG" => "Carnegie Classification 2010: Undergraduate Instructional Program", //CCIPUG
        "CCIPGRAD" => "Carnegie Classification 2010: Graduate Instructional Program", //CCIPGRAD
        "CCUGPROF" => "Carnegie Classification 2010: Undergraduate Profile", //CCUGPROF
        "CCENRPRF" => "Carnegie Classification 2010: Enrollment Profile", //CCENRPRF
        "CCSIZSET" => "Carnegie Classification 2010: Size and Setting", //CCSIZSET
        "CARNEGIE" => "Carnegie Classification 2000", //CARNEGIE
        "LANDGRNT" => "Land Grant Institution", //LANDGRNT
        "INSTSIZE" => "Institution size category", //INSTSIZE
        "CBSA" => "Core Based Statistical Area (CBSA)", //CBSA
        "CBSATYPE" => "CBSA Type Metropolitan or Micropolitan", //CBSATYPE
        "CSA" => "Combined Statistical Area (CSA)", //CSA
        "NECTA" => "New England City and Town Area (NECTA)", //NECTA
        "F1SYSTYP" => "Multi-institution or multi-campus organization", //F1SYSTYP
        "F1SYSNAM" => "Name of multi-institution or multi-campus organization", //F1SYSNAM
        "F1SYSCOD" => "Identification number of multi-institution or multi-campus organization", //F1SYSCOD
        "COUNTYCD" => "Fips County code", //COUNTYCD
        "COUNTYNM" => "County name", //COUNTYNM
        "CNGDSTCD" => "Congressional District code", //CNGDSTCD
        "LONGITUD" => "Longitude Location"); //LONGITUD
    /**
	 * Header Names
	 * Contains an array containing the header names.
	 */
	public static $headerNames = array(
        0 => "UNITID", //UNITID
        1 => "INSTNM", //INSTNM
        2 => "ADDR", //ADDR
        3 => "CITY", //CITY
        4 => "STABBR", //STABBR
        5 => "ZIP", //ZIP
        6 => "FIPS", //FIPS
        7 => "OBEREG", //OBEREG
        8 => "CHFNM", //CHFNM
        9 => "CHFTITLE", //CHFTITLE
        10 => "GENTELE", //GENTELE
        11 => "FAXTELE", //FAXTELE
        12 => "EIN", //EIN
        13 => "OPEID", //OPEID
        14 => "OPEFLAG", //OPEFLAG
        15 => "WEBADDR", //WEBADDR
        16 => "ADMINURL", //ADMINURL
        17 => "FAIDURL", //FAIDURL
        18 => "APPLURL", //APPLURL
        19 => "NPRICURL", //NPRICURL
        20 => "SECTOR", //SECTOR
        21 => "ICLEVEL", //ICLEVEL
        22 => "CONTROL", //CONTROL
        23 => "HLOFFER", //HLOFFER
        24 => "UGOFFER", //UGOFFER
        25 => "GROFFER", //GROFFER
        26 => "HDEGOFR1", //HDEGOFR1
        27 => "DEGGRANT", //DEGGRANT
        28 => "HBCU", //HBCU
        29 => "HOSPITAL", //HOSPITAL
        30 => "MEDICAL", //MEDICAL
        31 => "TRIBAL", //TRIBAL
        32 => "LOCALE", //LOCALE
        33 => "OPENPUBL", //OPENPUBL
        34 => "ACT", //ACT
        35 => "NEWID", //NEWID
        36 => "DEATHYR", //DEATHYR
        37 => "CLOSEDAT", //CLOSEDAT
        38 => "CYACTIVE", //CYACTIVE
        39 => "POSTSEC", //POSTSEC
        40 => "PSEFLAG", //PSEFLAG
        41 => "PSET4FLG", //PSET4FLG
        42 => "RPTMTH", //RPTMTH
        43 => "IALIAS", //IALIAS
        44 => "INSTCAT", //INSTCAT
        45 => "CCBASIC", //CCBASIC
        46 => "CCIPUG", //CCIPUG
        47 => "CCIPGRAD", //CCIPGRAD
        48 => "CCUGPROF", //CCUGPROF
        49 => "CCENRPRF", //CCENRPRF
        50 => "CCSIZSET", //CCSIZSET
        51 => "CARNEGIE", //CARNEGIE
        52 => "LANDGRNT", //LANDGRNT
        53 => "INSTSIZE", //INSTSIZE
        54 => "CBSA", //CBSA
        55 => "CBSATYPE", //CBSATYPE
        56 => "CSA", //CSA
        57 => "NECTA", //NECTA
        58 => "F1SYSTYP", //F1SYSTYP
        59 => "F1SYSNAM", //F1SYSNAM
        60 => "F1SYSCOD", //F1SYSCOD
        61 => "COUNTYCD", //COUNTYCD
        62 => "COUNTYNM", //COUNTYNM
        63 => "CNGDSTCD", //CNGDSTCD
        64 => "LONGITUD" //LONGITUD
    );

}

$page = new Page();
?>
</body>
</html>