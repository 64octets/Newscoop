B_HTML
INCLUDE_PHP_LIB(<*..*>)
B_DATABASE

CHECK_BASIC_ACCESS
CHECK_ACCESS(<*ManagePub*>)

B_HEAD
	X_TITLE(<*Add new publication*>)
<?php  if ($access == 0) { ?>dnl
	X_AD(<*You do not have the right to add publications.*>)
<?php  } ?>dnl
E_HEAD

<?php  if ($access) { ?>dnl
B_STYLE
E_STYLE

B_BODY

B_HEADER(<*Add new publication*>)
B_HEADER_BUTTONS
X_HBUTTON(<*Publications*>, <*pub/*>)
X_HBUTTON(<*Home*>, <*home.php*>)
X_HBUTTON(<*Logout*>, <*logout.php*>)
E_HEADER_BUTTONS
E_HEADER

<?php 
	query ("SELECT Unit, Name FROM TimeUnits WHERE 1=0", 'q_unit');
	query("SELECT Id as IdLang FROM Languages WHERE code='$TOL_Language'", 'q_def_lang');
	if($NUM_ROWS == 0){
		query("SELECT IdDefaultLanguage as IdLang FROM Publications WHERE Id = 1", 'q_def_lang');
	}
	fetchRow($q_def_lang);
	$IdLang = getVar($q_def_lang,'IdLang');
?>

<P>
B_DIALOG(<*Add new publication*>, <*POST*>, <*do_add.php*>)
	B_DIALOG_INPUT(<*Name*>)
		<INPUT TYPE="TEXT" NAME="cName" SIZE="32" MAXLENGTH="255">
	E_DIALOG_INPUT
	B_DIALOG_INPUT(<*Site*>)
		<INPUT TYPE="TEXT" NAME="cSite" VALUE="<?php  pencHTML($HTTP_HOST); ?>" SIZE="32" MAXLENGTH="255">
	E_DIALOG_INPUT
	B_DIALOG_INPUT(<*Default language*>)
	    <SELECT NAME="cLanguage">
	    <?php 
		query ("SELECT Id, OrigName FROM Languages", 'q_lang');
		    $nr=$NUM_ROWS;
		    for($loop=0;$loop<$nr;$loop++) {
			fetchRow($q_lang);
			pcomboVar(getVar($q_lang,'Id'),getVar($q_def_lang,'IdLang'),getVar($q_lang,'OrigName'));
		    }
	    ?>dnl
	    </SELECT>
	E_DIALOG_INPUT
	B_DIALOG_INPUT(<*Pay Period*>)
		<INPUT TYPE="TEXT" NAME="cPayTime" VALUE="" SIZE="5" MAXLENGTH="5">
	E_DIALOG_INPUT
	B_DIALOG_INPUT(<*Time Unit*>)
	    <SELECT NAME="cTimeUnit">
<?php 
	$q = "SELECT t.Unit, t.Name FROM TimeUnits as t, Languages as l WHERE t.IdLanguage = l.Id and l.Code = '" . $TOL_Language . "' order by t.Unit asc";
	query($q, 'q_unit');
	$nr = $NUM_ROWS;
	if ($nr == 0) {
		$q = "SELECT t.Unit, t.Name FROM TimeUnits as t, Languages as l WHERE t.IdLanguage = l.Id and l.Code = 'en' order by t.Unit asc";
		query($q, 'q_unit');
		$nr = $NUM_ROWS;
	}
	for($loop=0;$loop<$nr;$loop++) {
		fetchRow($q_unit);
		pcomboVar(getVar($q_unit,'Unit'),getVar($q_pub,'TimeUnit'),getVar($q_unit,'Name'));
	}
?>dnl
	    </SELECT>
	E_DIALOG_INPUT
	B_DIALOG_INPUT(<*Unit Cost*>)
		<INPUT TYPE="TEXT" NAME="cUnitCost" VALUE="" SIZE="20" MAXLENGTH="32">
	E_DIALOG_INPUT
	B_DIALOG_INPUT(<*Currency*>)
		<INPUT TYPE="TEXT" NAME="cCurrency" VALUE="" SIZE="20" MAXLENGTH="32">
	E_DIALOG_INPUT
	B_DIALOG_INPUT(<*Paid Period*>)
		<INPUT TYPE="TEXT" NAME="cPaid" VALUE="" SIZE="20" MAXLENGTH="32">
	E_DIALOG_INPUT
	B_DIALOG_INPUT(<*Trial Period*>)
		<INPUT TYPE="TEXT" NAME="cTrial" VALUE="" SIZE="20" MAXLENGTH="32">
	E_DIALOG_INPUT
	B_DIALOG_BUTTONS
		SUBMIT(<*Save*>, <*Save changes*>)
		REDIRECT(<*Cancel*>, <*Cancel*>, <*X_ROOT/pub/*>)
	E_DIALOG_BUTTONS
E_DIALOG
<P>

X_HR
X_COPYRIGHT
E_BODY
<?php  } ?>dnl

E_DATABASE
E_HTML
