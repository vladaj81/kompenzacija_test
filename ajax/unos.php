<?php
	session_start();
	$root = $_SERVER ["DOCUMENT_ROOT"];
	require_once "$root/common/no_cache.php";
	require_once "$root/privilegije/privilegije.php";
	require_once "$root/common/zabrane.php";
	$sifra_u_nizu = array('001001001');
	$sifra_provera = implode("','",$sifra_u_nizu);
	zabrana_istekla_sesija($sifra_provera, $root);
	 
	require "../../common/no_cache.php";
	session_start();
	if (isset($_SESSION['radnik']) && $_SESSION['radnik'])
	{
		$radnik = $_SESSION['radnik'];
	}
	else
	{
	  session_destroy();
	  header("Location: ../../common/login.php");
	  exit;
	}
?>
<html>
	<head>
		<title>Knjiženje finansijskog naloga</title>
		<meta name="naslov" content="Knjiženje finansijskog naloga">
		<meta http-equiv="Content-Type" content="text/html; charset=iso8859-2">
		<meta http-equiv="Content-Script-Type" content="text/javascript">
		<link rel="stylesheet" type="text/css" href="../../common/menistil.css">
		<link rel="stylesheet" href="../../js/jquery-ui.css">
		<link href="../../stete/zapisnik_dodatno/jquery-ui.css" rel="stylesheet" type="text/css" />
		<link rel="stylesheet" type="text/css" href="../../css/stil1.css">
		<script type="text/javascript" language="javascript" src="../../js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="../../js/jquery-ui.js"></script>
		<script src="../../stete/zapisnik_dodatno/jquery.min.js"></script>
		<script src="../../stete/zapisnik_dodatno/jquery-ui.min.js"></script>

		<script type="text/javascript">
		/***********************************************
		* Disable "Enter" key in Form script- By Nurul Fadilah(nurul@REMOVETHISvolmedia.com)
		* This notice must stay intact for use
		* Visit http://www.dynamicdrive.com/ for full source code
		* Slightly changed by Zoran Pantelić (Zoran.Pantelic@ams.co.yu)
		***********************************************/
		function handleEnter (field, event) {
				var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
				if (keyCode == 13) {
					var i;
					for (i = 0; i < field.form.elements.length; i++)
						if (field == field.form.elements[i])
							break;
					i = (i + 1) % field.form.elements.length;
					if (i == 3) { i = i+1; }
					field.form.elements[i].focus();
					return false;
				}
				else
				return true;
			}
		</script>
		<script language="javascript" src="../../common/cal2.js">
		/*
		Xin's Popup calendar script- Xin Yang (http://www.yxscripts.com/)
		Script featured on/available at http://www.dynamicdrive.com/
		This notice must stay intact for use
		*/
		</script>
	    <script language="javascript" src="../../common/cal_fink.js"></script>
	    <script type="text/javascript">

		$(document).ready(function() 
		{
			$("#predlog_knjizenja").hide();
            $("#predlog").hide();

            $('select[name="pdv_sifra"]').change(function() 
			{
				var val = $(this).val();
                var option = $(this).find('option[value="'+val+'"]');
                var ima_pdv = parseInt(option.attr('ima-pdv'));
                if(ima_pdv > 0) 
                {
                    $('#prikazi-ako-ima-pdv').show();
                }
                else 
                {
                    $('#prikazi-ako-ima-pdv').hide();
                }

            });

			function provera_konta_za_razbijanje()
			{
				$("input[name=submit]").attr('disabled', false);
				//Uzimanje vrednosti konta sa forme
				var konto = $("input[name=konto]").val();
				//Ukoliko je dužina konta jednaka 5, vrednost konta se prosledjuje funkciji proveri_konto()
				if(konto.length === 5)
				{
					$.ajax({
						url:"knjizenje_razbijenih_troskova_funkcije.php?funkcija=proveri_konto",
					    type:"POST",
					    datatype:"json",
					   	data: 
						   	{ 
					   			konto: konto
							},
							success: function(ret) 
							{ 			
				 				var data = JSON.parse(ret);  
				 				var flag = data.flag;
				 				//Ukoliko je flag postavljen na true, prikazuje se padajuća lista sa ponuđenim šablonima za raspodelu
				 				if(flag == true)
					 			{
				 					$("#predlog_knjizenja").show();
				 					$("#predlog").show();
				 					$("input[name=submit]").attr('disabled', true);
						 		}
							}
						});
				} 
				else 
				{
					$("#predlog_knjizenja").hide();
				}
			}

			// Dodao Nemanja Jovanovic
			function opredeli_pdv()
			{
				var konto 		= $("input[name=konto]").val();
				var pib 		= $("input[name=partner]").val();
				var potrazuje 	= parseInt($("input[name=potrazuje]").val());
				
				if(konto.length > 4 && potrazuje > 0)
				{
					$.ajax({
						url:"knjizenje_razbijenih_troskova_funkcije.php?funkcija=opredeli_pdv",
					    type:"POST",
					    datatype:"json",
					   	data: 
						{ 
					   		konto: konto,
					   		pib: pib
						},
						success: function(ret) 
						{ 			
				 			var data = JSON.parse(ret);  
				 			var flag = data.flag;
							var value = data.value;
				 			
				 			if(flag)
					 		{
				 				$("#pdv_sifra").val(value);
						 	}
						}
					});
				}
			}

			$("input[name=konto]").blur(function(){
				provera_konta_za_razbijanje();
			});

			$("input[name=potrazuje]").blur(function(){
				opredeli_pdv();
			});
			
			
			$("#predlog").click(function()
			{
				//Podaci sa forme koji se prosledjuju php funkciji predlog_knjizenja()
				var datum_knjizenja = $("input[name=datknjiz]").val();
				var vrsta_dokumenta = $("select[name=vrstadok]").val();
				var broj_naloga = $("input[name=brojnaloga]").val();
				var raspodela = $("input[name=raspodela]").val();
				var konto = $("input[name=konto]").val();
				var raspodela = $("select[name=raspodela]").val();
				var opis_dokumenta = $("input[name=opisdok]").val();
				var broj_dokumenta = $("input[name=brojdok]").val();
				var datum_dokumenta = $("input[name=datdok]").val();
				var dospeva = $("input[name=dospelo]").val();
				var duguje = $("input[name=duguje]").val();
				var potrazuje = $("input[name=potrazuje]").val();
				var mesto_nastanka_troska = $("select[name=mnt]").val();
	
				//provera polja potražuje, ukoliko je uneta neka vrednost ispisuje se poruka o grešci.
				//potražna strana se ne unosi za konta 54
				if(potrazuje > 0)
				{
					alert("Nije moguće knjiženje na potražnoj strani za uneti konto.");
					return;
				}
					
				
				$("#predlog_modal").html("");
				//kreiranje ajax funkcije, podaci sa forme se prosledjuju php funkciji napravi_predlog()
				$.ajax({
					url:"knjizenje_razbijenih_troskova_funkcije.php?funkcija=napravi_predlog",
				    type:"POST",
				    datatype:"json",
				   	data: 
					{ 
				   		 datum_knjizenja: datum_knjizenja,
						 vrsta_dokumenta: vrsta_dokumenta,
						 broj_naloga: broj_naloga,
						 raspodela: raspodela,
						 konto: konto,
						 raspodela: raspodela,
						 opis_dokumenta: opis_dokumenta,
						 broj_dokumenta: broj_dokumenta,
						 datum_dokumenta: datum_dokumenta,
						 dospeva: dospeva,
						 duguje: duguje,
						 potrazuje: potrazuje,
						 mesto_nastanka_troska: mesto_nastanka_troska,
					},
					success: function(ret) 
					{ 		
			 			var data = JSON.parse(ret); 
			 			var poruka = data.poruka;
			 			var flag = data.flag;
			 			var konto_postoji = data.konto_postoji;
			 			//Ukoliko je flag postavljen na true prikazuje se modal sa predlog za knjiženje
			 			if(flag == true)
				 		{
					 		//niz prosledjen od strane php funkcije napravi_predlog()
			 				var podaci_niz =  data.podaci_niz;
			 				//upisivanje podataka u div
				 			$("#predlog_modal").append(data.table);
				 			//kreiranje jQuery modal dialog-a
				 			$("#predlog_modal").dialog({
				 				title: "PREDLOG KNJIŽENJA",
				 				closeOnEscape: false,
				 				modal: true,
				 				height: 500,
				 				width: 1080,
				 				resizable: false,
				 				position: { my: "top", at: "top", of: window },
				 				buttons: 
					 			{
				 				    Zatvori: function() 
				 				    {
				 				    	$( this ).dialog( "close" );
				 				    },
				 				    Knjiži: function()
				 				    {
					 				    //Ukoliko konto ne postoji u bazi ispisuje se poruka o grešci
				 				    	if(konto_postoji == false)
					 				    {
					 				    	alert("Postoji konto na koji nije moguce knjižiti.");
					 				        $( this ).dialog("close"); 	
				 				        } 
				 				        //U suprotno podaci se prosleđuju ajax funkcijom, funkciji izvrsi_knjiženje() 
			 				        	else 
				 				        {
				 				        	$.ajax({
												url:"knjizenje_razbijenih_troskova_funkcije.php?funkcija=izvrsi_knjizenje",
												type:"POST",
												datatype:"json",
												data: 
												{ 
												   	podaci_niz: podaci_niz,
												   	konto: konto
												},
												success: function(ret) 
												{ 
													var data = JSON.parse(ret);  
											 		var flag = data.flag;
											 		//Ukoliko je flag postavljen na true ispisuje se poruka da su podaci uspešno upisani u bazu
											 		//broj naloga i vrsta dokumenta se prosedjuju formi za knjiženje
											 		if(flag == true)
											 		{
												 		alert("Upisivanje uspešno.");
												 		var vrstadok = data.vrsta_dok;
												 		var brojnaloga = data.broj_naloga;
												 		var datknjiz = data.datknjiz;
												 		var datdok = data.datdok;
												 		var opisdok = data.opisdok;
												 		var brojdok = data.brojdok;
												 		
												 		window.open('unos.php?datknjiz='+datknjiz+'&datdok='+datdok+'&brojnaloga='+brojnaloga+'&vrstadok='+vrstadok+'&opisdok='+opisdok+'&brojdok='+brojdok,'main');
											 		}
											 		//Ukoliko je flag postavljen na false ispisuje se poruka upisivanje u bazu nije izvršeno
											 		else
											 		{
											 			alert("Upisivanje neuspepšno.");
											 		}
													$( this ).dialog("close"); 	
												}
												}); // zatvorena ajax funkcije koja prosledjuje podatke za knjiženje
				 				        	}
				 				      	} // zatvorena funkcija u okviru dugmeta Knjiži
				 				    }
				 				}); // jQuery modal dialog  
				 		}
			 			else
			 			{
				 			//Ukoliko je flag postavljen na false ispisuje se poruka o greškama
				 			alert(poruka);
			 			}
					} // zatvorena success funkcija
				}); // zatvorena ajax funkcija za kreiranje predloga
			});	// zatvorena click funkcija 	

					
		});
	</script>
	<!-- Knjizenje (KRAJ) - Nemanja Jovanovic - 2017-06-05 -->
</head>
<body bgColor=#F2F4F9 topmargin="5">

<div align="center">
	<table border="0" style="border-collapse: collapse" width="100%" id="table259">
		<tr>
			<td align="center">
            <table class=tbt cellSpacing=0 cellPadding=0 border=0 id="table260" width="100%">
              <tbody>
              <tr>
                <td class=tbtl width="22">
			<img height=22 alt="" src="../../images/icg/tb2_l.gif" width=22>
		</td>
                <td class=tbtbot style="background-image: url('../../images/icg/tb2_m.gif')">
			<b><font color="#FF6500" face="Verdana">Knjiženje finansijskog naloga</font></b>
		</td>
                <td class=tbtbot style="background-image: url('../../images/icg/tb2_m.gif')">&nbsp;</td>
                <td class=tbtr width="124">
									<img height=22 alt="" src="../../images/icg/tb2_r.gif" width=124>
                </td>
              </tr>
              </tbody>
            </table>
            <table border="1" cellpadding="5" id="table261" width="100%" bgcolor="#FFFFFF" style="border-collapse: collapse" bordercolorlight="#C0C0C0" bordercolordark="#C0C0C0" cellspacing="5">
<tr>
<td>

<?php

date_default_timezone_set('Europe/Belgrade');

/* Kada se uspešno izvrši upis, neke vrednosti se prenose
 * dalje u sledeći ekran GET metodom.
 * Normalno se promenljive prenose POST metodom.
 */
if (isset($_GET)) {
	foreach ($_GET as $kljuc=>$vrednost) {
		//Izmenio Nemanja Jovanovic - 2020-03-24
		//${$kljuc} = mb_convert_encoding($vrednost,"UTF-8");
		${$kljuc} = $vrednost;
	}
}

if (isset($_POST)) {
	foreach ($_POST as $kljuc=>$vrednost) {
		${$kljuc} = $vrednost;
	}
}

$conn = pg_connect ("host=localhost dbname=amso user=zoranp");
if (!$conn) 
{
	echo "Greška otvaranja konekcije prema SQL serveru.";
	exit;
}

$conn1 = pg_connect ("host=localhost dbname=osnovna user=zoranp");
if (!$conn1) 
{
	echo "Greška otvaranja konekcije prema SQL serveru.";
	exit;
}

function polje_za_unos($opis, $ime, $duzina, $pamti, $vrednost, $desni)
{
echo "<td align=\"right\">";
echo $opis . ": ";
echo "</td>\n<td";
if ($desni > 0) { echo " colspan=\"$desni\""; }
echo ">";
echo "<input name=\"$ime\" type=\"text\"";
if ($pamti == 1) { echo " value=\"" . $vrednost . "\""; }
echo " size=\"$duzina\" class=\"main\" onkeypress=\"return handleEnter(this, event)\">\n";
echo "</td>\n";
}

function polje_datuma($opis, $ime, $duzina, $pamti, $vrednost, $desni)
{
echo "<tr><td align=\"right\">";
echo $opis . ": ";
echo "</td>\n<td";
if ($desni > 0) { echo " colspan=\"$desni\""; }
echo ">";
echo "<input name=\"$ime\" type=\"text\"";
if ($pamti == 1) { echo " value=\"" . $vrednost . "\""; }
echo " size=\"$duzina\" class=\"main\" ";
echo " onclick=\"showCal('$ime')\" onkeypress=\"return handleEnter(this, event)\">\n";
echo "&nbsp;&nbsp;<font color=\"#CC0000\">Za izbor/promenu/brisanje datuma klikni u polje.</font>";
echo "</td>\n</tr>\n";
}

function je_vreme($vrednost)
{
$niz = explode(":", $vrednost);
if (strlen($vrednost) == 5 && count($niz) == 2 && $niz[0] < 24 && $niz[1] < 60) {
    return true;
    }
    else {
    return false;
    }
}

function je_datum($vrednost)
{
$niz = explode("-", $vrednost);
$dan = date ("Y-m-d", mktime (0,0,0,$niz[1],$niz[2],$niz[0]));
if ($vrednost == $dan) {
    return true;
    }
    else {
    return false;
    }
}

function drop_kombo($opis, $ime, $conn, $tabela, $vraca1, $vraca2, $vrednost, $desni)
{
$rezultat = pg_query ($conn, "SELECT $vraca1, $vraca2 FROM $tabela ORDER BY $vraca1");
if (!$rezultat) {
    echo "<br><br>Došlo je do greške prilikom konektovanja.\n";
    exit;
}
$polja = pg_num_fields($rezultat);
$redova = pg_num_rows($rezultat);

echo "<td align=\"right\">";
echo $opis . ": ";
echo "</td>\n<td";
if ($desni > 0) { echo " colspan=\"$desni\""; }
echo ">";

echo "<select name=\"$ime\">\n";

for ($a=0; $a < $redova; $a++) {
$arr = pg_fetch_assoc ($rezultat);
for ($j=0; $j < $polja; $j++) {
    if (pg_field_name($rezultat, $j) == $vraca1) {
    echo "<option ";
    if ($vrednost == $arr[$vraca1]) { echo "selected "; }
    echo "value = \"". $arr[$vraca1] . "\" ";

    echo ">\n";

    $sadrzaj_polja = $arr[$vraca1] . " ";
    }
    else {
        $sadrzaj_polja .= " &ndash; " . $arr[$vraca2];
	echo $sadrzaj_polja . "</option>\n";
    }
}
}

echo "</select>";

echo "</td>\n";
}

function drop_komboMNT($opis, $ime, $conn, $tabela, $vraca1, $vraca2, $vrednost, $desni)
{
//$sql = "SELECT $vraca1, $vraca2 FROM $tabela WHERE sifra ~ E'^1[1-3][1-8][0-79][0-9]{2,2}$' ORDER BY $vraca1 ";
	if($tabela == 'partneri')
	{
$sql = "SELECT $vraca1, $vraca2 FROM $tabela WHERE sifra LIKE '______' AND sifra not like '___8__' AND sifra between '111000' AND '199999' ORDER BY $vraca1 ";
$rezultat = pg_query ($conn, $sql);
if (!$rezultat) {
    echo "<br><br>Došlo je do greške prilikom konektovanja.\n";
    exit;
}
	}
	else if($tabela == 'kanali_prodaje')
	{
		$sql = "SELECT * FROM sifarnici.kanali_prodaje";
		$rezultat = pg_query ($conn, $sql);
		if (!$rezultat) {
			echo "<br><br>Došlo je do greške prilikom konektovanja.\n";
			exit;
		}
	}
$polja = pg_num_fields($rezultat);
$redova = pg_num_rows($rezultat);

echo "<td align=\"right\">";
echo $opis . ": ";
echo "</td>\n<td";
if ($desni > 0) { echo " colspan=\"$desni\""; }
echo ">";

echo "<select name=\"$ime\">\n";
	if($tabela == 'kanali_prodaje')
	{
		echo "<option value='0'>Izaberite kanal prodaje</option>";
	}

for ($a=0; $a < $redova; $a++)
{
    $arr = pg_fetch_assoc ($rezultat);
    for ($j=0; $j < $polja; $j++)
    {
    if (pg_field_name($rezultat, $j) == $vraca1) 
    {
    echo "<option ";
    if ($vrednost == $arr[$vraca1]) { echo "selected "; }
    echo "value = \"". $arr[$vraca1] . "\" ";

    echo ">\n";

    $sadrzaj_polja = $arr[$vraca1] . " ";
    }
    else 
    {
		$sadrzaj_polja .= " &ndash; " . $arr[$vraca2];
		echo $sadrzaj_polja . "</option>\n";
    }
}
}

echo "</select>";

echo "</td>\n";
}

function dopuni_nulama($prom,$duzina) {
$i = $duzina - strlen($prom);
for($j=0; $j<$i; $j++){
$prom='0' . $prom;
}
return($prom);
}

require_once ('../../common/zakljucano.php');

// Ovde pocinje kod...

echo "<form action=\"" . htmlentities($_SERVER['PHP_SELF']) . "\" name=\"knjizenje\" method=\"post\" accept-charset=\"iso8859-2\">\n\n";

if(!isset($datknjiz) || !$datknjiz) {
// $datknjiz = date("Y-m-d", mktime(0,0,0,date("m"),date("d"),date("Y")));
$datknjiz = date("Y-m-d");
}

if (!isset($duguje) || !$duguje) { $duguje = '0.0'; }
if (!isset($potrazuje) || !$potrazuje) { $potrazuje = '0.0'; }

/*
// poslednji kvaratl 2013 i cela 2014
if (substr($datknjiz, 0, 4) == '2016') {
  $zakljucano = '2016-01-03';
  $godina = '2016';
  $k_plan = 'k_2016';
}
else if (substr($datknjiz, 0, 4) == '2015'){
  $zakljucano = '2015-10-01';
  $godina = '2015';
  $k_plan = 'k_2015';
}
*/

/* Novi način zaključavanja...
 * 05.02.2015. zp
 */
if ($datknjiz != proveriZDatum ('KVART', $datknjiz, $conn)) {
	die("Ne može se knjižiti u ovom periodu. Proverite datum naloga.");
}
$godina = substr($datknjiz, 0, 4);
$k_plan = 'k_' . $godina;

echo "<table>\n";
// echo "<tr>\n";

$opis = 'Datum knjiženja ';
$ime = 'datknjiz';
$duzina = '15';

polje_datuma($opis, $ime, $duzina, 1, $datknjiz, 3);

echo "</tr>\n";
echo "<tr>\n";

$opis = 'Vrsta dokumenta ';
$ime = 'vrstadok';
$tabela = 'vrsta';
$vraca1 = 'vrstadok';
$vraca2 = 'opis';

if (!isset($vrstadok) || !$vrstadok) { $vrstadok = 'IZ'; }

drop_kombo($opis, $ime, $conn, $tabela, $vraca1, $vraca2, $vrstadok, 3);

echo "</tr>\n";
echo "<tr>\n";

if(isset($brojnaloga) && $brojnaloga) {
	$brojnaloga = preg_replace("/\ {1,}/",'',$brojnaloga);
}
else {
	$brojnaloga = '';
}

$opis = 'Broj naloga ';
$ime = 'brojnaloga';
$duzina = '15';

polje_za_unos($opis, $ime, $duzina, 1, $brojnaloga, 3);

echo "</tr>\n";
echo "<tr>\n";

$opis = "<font color=\"red\">Pomoć </font>";
$ime = 'tekrac';
$duzina = '40';

$tekrac = isset($tekrac) ? $tekrac : '';

polje_za_unos($opis, $ime, $duzina, 1, $tekrac, 3);

echo "<td colspan=\"1\">&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Pomoć\" class=\"button\" name=\"submit\">\n";

echo "</tr>\n";
echo "<tr>\n";

if (!isset($submit) || $submit != "Pomoć") {

$opis = 'Šifra partnera ';
$ime = 'partner';
$duzina = '15';

$partner = isset($partner) ? $partner : '';

polje_za_unos($opis, $ime, $duzina, 1, $partner, 0);

echo "<td colspan=\"2\">";
if ($partner) {
$sql="SELECT naziv FROM partneri WHERE sifra='$partner'";
$result=pg_query($conn, $sql);
if (!$result) {
    echo "Greška u određivanju broja relevantnih zapisa.\n";
    exit;
}
$arr = pg_fetch_assoc($result);
$naziv = $arr['naziv'];
echo "<font color=\"navy\">" . $naziv . "</font>\n";
}
else {
echo "&nbsp";
}
echo "</td>\n";
echo "<br><br>";

}
else {

if (!$tekrac || strlen($tekrac) < 4) {
echo "<script type=\"text/javascript\">";
echo "alert(\"Aman ženo/čoveče, postavi neki normalan uslov!\")\n";
echo "document.knjizenje.tekrac.focus();\n";
echo "</script>";
}

else {

$prvi = preg_replace("/(\ +)/",'',$tekrac);

if (ereg("[0-9]{3,3}[\-][0-9]{1,}[\-][0-9]{2,2}", $prvi)) {
$nasao = explode('-', $prvi);
$nasao[1] = str_repeat('0', 13 - strlen($nasao[1])) . $nasao[1];

$konn = pg_connect ("host=localhost dbname=jpc user=zoranp");
if (!$conn) {
	echo "Greška otvaranja konekcije prema SQL serveru.";
	exit;
	}

$sql = "SELECT pib, naziv, ulica || ' ' || broj AS adresa, posbroj, mesto FROM (select * from pracun UNION ALL select * from sracun) AS a, (select matbr, pib, naziv, mesto, opstina, posbroj, ulica, broj, sifdel, pdv from pravna UNION ALL select * from str) AS b where a.matbr=b.matbr AND tek_racun = '";
$sql .= implode('', $nasao);
$sql .= "'";
$result = pg_query($konn, $sql);

$arr = pg_fetch_row($result);

$n_pib = $arr[0];
$n_naziv = substr($arr[1], 0, 40);
$n_adresa = substr($arr[2], 0, 30);
$n_posbroj = $arr[3];
$n_mesto = substr($arr[4], 0, 20);

pg_close($konn);

if (ereg("[1-2][0-9]{8,8}", $n_pib) && $arr['0']) {
$upit = "SELECT * from partneri WHERE sifra = '$n_pib'";
$rez = pg_query($conn, $upit);
$arr = pg_fetch_assoc($rez);
if (!$arr['sifra']) {
$upit = "INSERT INTO partneri (sifra, naziv, adresa, posbroj, mesto, tekracun) VALUES ('$n_pib', '$n_naziv', '$n_adresa', $n_posbroj, '$n_mesto', '$prvi')";
$rez = pg_query($conn, $upit);
if ($rez) {
echo "<script language=\"javascript\">\n";
echo "alert(\"Podaci o partneru uspešno upisani!\")\n";
echo "</script>\n";
}
}
else {
echo "<script language=\"javascript\">\n";
echo "alert(\"Partner postoji!\")\n";
echo "</script>\n";
}
$partner = $n_pib;
}

else {
echo "<script language=\"javascript\">\n";
echo "alert(\"Partner ne postoji u registru pravnih lica!\")\n";
echo "</script>\n";
}

$opis = 'Šifra partnera ';
$ime = 'partner';
$duzina = '15';

polje_za_unos($opis, $ime, $duzina, 1, $partner, 0);
echo "<td colspan=\"2\">";
echo "<font color=\"navy\">" . $n_naziv . "</font></td>\n";

}

else {

$sql = "SELECT sifra, naziv FROM partneri WHERE upper(naziv) like upper('%$tekrac%') ORDER BY naziv";
$rezultat = pg_query ($conn, $sql);
if (!$rezultat) {
    echo "<br><br>Došlo je do greške prilikom konektovanja.\n";
    exit;
}
$polja = pg_num_fields($rezultat);
$redova = pg_num_rows($rezultat);

echo "<td align=\"right\">Šifra partnera </td>\n";
echo "<td colspan=\"3\" align=\"left\">\n";

echo "<select name=\"partner\">\n";

for ($a=0; $a < $redova; $a++) {
$arr = pg_fetch_assoc ($rezultat);
for ($j=0; $j < $polja; $j++) {
    if (pg_field_name($rezultat, $j) == "sifra") {
    echo "<option ";
    if ($vrednost == $arr['sifra']) { echo "selected "; }
    echo "value = \"". $arr['sifra'] . "\" ";

    echo ">\n";

    $sadrzaj_polja = $arr['sifra'] . " ";
    }
    else {
        $sadrzaj_polja .= " &ndash; " . $arr['naziv'];
	echo $sadrzaj_polja . "</option>\n";
    }
}
}

echo "</select>";

echo "</td>\n";
echo "<br><br>";
}
}
}

echo "</tr>\n";
echo "<tr>\n";

if (isset($submit) && $submit == 'Konto' && ($konto || $konto == '0')) {

$sql = "SELECT konto, opis1 || CASE when opis2 notnull THEN ' ' || opis2 ELSE '' END AS opis FROM $k_plan WHERE konto LIKE '$konto%' AND slovon isnull ";

if ($partner) {
$sql .= "AND anasin = 'S' ";
}
else {
$sql .= "AND anasin = 'A' ";
}

$sql .= "ORDER BY konto || 'A'";

// echo $sql;

$result = pg_query ($conn, $sql);
if (!$result) {
    echo "<br><br>Došlo je do greške prilikom konektovanja.\n";
    exit;
}
$polja = pg_num_fields($result);
$redova = pg_num_rows($result);

echo "<td align=\"right\">Konto </td>\n";
echo "<td colspan=\"4\" align=\"left\">\n";

echo "<select name=\"konto\">\n";

for ($a=0; $a < $redova; $a++) {
$arr = pg_fetch_assoc ($result);
for ($j=0; $j < $polja; $j++) {
    if (pg_field_name($result, $j) == "konto") {
    echo "<option ";
    if ($konto == $arr['konto']) { echo "selected "; }
    echo "value = \"". $arr['konto'] . "\" ";

    echo ">\n";

    $sadrzaj_polja = $arr['konto'] . " ";
    }
    else {
        $sadrzaj_polja .= " &ndash; " . $arr['opis'];
	echo $sadrzaj_polja . "</option>\n";
    }
}
}

echo "</select>";

echo "</td>\n";
// echo "<br><br>";

}

else {

$opis = 'Konto ';
$ime = 'konto';
$duzina = '15';

$konto = isset($konto) ? $konto : '';

polje_za_unos($opis, $ime, $duzina, 1, $konto, 3);

echo "<td colspan=\"1\">&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Konto\" class=\"button\" name=\"submit\">\n";

}

echo "</tr>\n";
	//Nemanja 29.05.2017
	echo "<tr id='predlog_knjizenja'><td align='right'>Sabloni:</td><td colspan='3'>";
		$raspodela_sql = "select * from sifarnici.sabloni_za_raspodelu_konta";
		$rezultat_sql = pg_query($conn, $raspodela_sql);
		$raspodela_rez = pg_fetch_all($rezultat_sql);
		$broj_raspodela = count($raspodela_rez);
		$prethodni = null;
		echo "<select style='width: 500px;' name='raspodela'>";
		for($i = 0; $i < $broj_raspodela; $i++)
		{
			if($prethodni != $raspodela_rez[$i]['raspodela'])
			{
				echo "<option value='".$raspodela_rez[$i]['raspodela']."'>".$raspodela_rez[$i]['raspodela'] . " ( ";
			}
			for($j = 0; $j < $broj_raspodela; $j++)
			{
				if($raspodela_rez[$i]['raspodela'] == $raspodela_rez[$j]['raspodela'])
				{
					echo $raspodela_rez[$j]['vrsta_osiguranja'] . " - " . $raspodela_rez[$j]['udeo'] . "% ";
				}
			}
			echo ")</option>";
			$prethodni = $raspodela_rez[$i]['raspodela'];
		}
		echo "</td>";
	echo "</tr>\n";
echo "<tr>\n";

$opis = 'Opis dokumenta ';
$ime = 'opisdok';
$duzina = '30';

$opisdok = isset($opisdok) ? $opisdok : '';

polje_za_unos($opis, $ime, $duzina, 1, ${$ime}, 0);

$opis = '&nbsp;&nbsp;&nbsp;Broj dokumenta ';
$ime = 'brojdok';
$duzina = '30';

$brojdok = isset($brojdok) ? $brojdok : '';

polje_za_unos($opis, $ime, $duzina, 1, ${$ime}, 0);

echo "</tr>\n";
echo "<tr>\n";

if(!isset($datdok) || !$datdok) {
$datdok = $datknjiz;
}

$opis = 'Datum dokumenta ';
$ime = 'datdok';
$duzina = '15';

polje_datuma($opis, $ime, $duzina, 1, ${$ime}, 3);

echo "</tr>\n";
echo "<tr>\n";

$opis = 'Rok dospeća (dana) ';
$ime = 'dospelo';
$duzina = '15';

$dospelo = isset($dospelo) ? $dospelo : '';

polje_za_unos($opis, $ime, $duzina, 1, ${$ime}, 3);

echo "<tr>\n";
echo "<tr>\n";

$opis = 'Duguje ';
$ime = 'duguje';
$duzina = '15';

polje_za_unos($opis, $ime, $duzina, 1, ${$ime}, 0);

$opis = 'Potražuje ';
$ime = 'potrazuje';
$duzina = '15';

polje_za_unos($opis, $ime, $duzina, 1, ${$ime}, 0);

echo "</tr>\n";
echo "<tr>\n";

$opis = 'Mesto troška ili prihoda ';
$ime = 'mnt';
$tabela = 'partneri';
$vraca1 = 'sifra';
$vraca2 = 'naziv';

$mnt = isset($mnt) ? $mnt : '';

drop_komboMNT($opis, $ime, $conn, $tabela, $vraca1, $vraca2, $mnt, 3);

echo "</tr>\n";

// dodato 2016-01-29 select lista za kanale prodaje - POCETAK
echo "<tr>\n";

$opis = 'Kanal prodaje ';
$ime = 'kanal_prodaje';
$tabela = 'kanali_prodaje';
$vraca1 = 'sifra';
$vraca2 = 'opis';

$mnt = isset($mnt) ? $mnt : '';

drop_komboMNT($opis, $ime, $conn, $tabela, $vraca1, $vraca2, $mnt, 3);

echo "</tr>\n";
// dodato 2016-01-29 select lista za kanale prodaje - KRAJ


// dodato 2018-11-20 select lista za odabir PDV-a - POCETAK
echo "<tr>\n";

//definisem kveri
$sql = "
SELECT
    naziv_evidencije,
    broj_evidencije,
    broj_stavke,
    naziv_stavke,
    pdv
FROM
    sifarnici.pdv_sifarnik
WHERE
    broj_evidencije IN ('2', '3', '3a', '8.a.', '8.b.', '8.v.', '8.g.', '8.d.')
GROUP BY
    redosled_evidencije,
    naziv_evidencije,
    broj_evidencije,
    broj_stavke,
    naziv_stavke,
    pdv
ORDER BY
   redosled_evidencije,
   broj_stavke
";

//izvrsavam kveri i ako nije kako treba ispisujem gresku
$rezultat = pg_query ($conn, $sql);
if (!$rezultat) {
    echo "<br><br>Došlo je do greške prilikom konektovanja.\n";
    exit;
}

//hvatam broj redova i broj polja
$polja = pg_num_fields($rezultat);
$redova = pg_num_rows($rezultat);

echo "<td align=\"right\">";
echo "PDV Šifra : ";
echo "</td>\n<td";
echo " colspan=\"3\"";
echo ">";

//definisem niz koji cu da punim za dropdown
$niz_za_dropdown = array();

for ($a=0; $a < $redova; $a++) 
{
    $arr = pg_fetch_assoc ($rezultat);
    if(!isset($niz_za_dropdown[$arr['broj_evidencije']])) 
    {
        $niz_za_dropdown[$arr['broj_evidencije']] = array(
            'naziv_evidencije'  => $arr['naziv_evidencije'],
            'broj_evidencije'   => $arr['broj_evidencije'],
            'stavke'            => array()
        );
    }
    //punim niz za dropdown
    $niz_za_dropdown[$arr['broj_evidencije']]['stavke'][] = $arr;

}

//stampam select
echo "<select name=\"pdv_sifra\" id='pdv_sifra'>\n";
echo "<option value='-1'>Izaberite PDV Šifru</option>";
echo "<option value='0'>Ne ide u POPDV prijavu</option>";

foreach($niz_za_dropdown as $br_evidenije => $evidencija_info) 
{

    $naziv_evidencije = $evidencija_info['naziv_evidencije'];

    echo "<optgroup label='". $br_evidenije . " - " . $naziv_evidencije . "'>";

    foreach($evidencija_info['stavke'] as $stavka) {

        $val_drop = $stavka['broj_evidencije'] . "||" . $stavka['broj_stavke'] . "||" . $stavka['pdv'];
        $broj_stavke = $stavka['broj_stavke'];
        $pdv = $stavka['pdv'];
        $pdv_prikaz = $pdv ? $pdv. "% - " : "";
        $ima_pdv = $pdv ? 1 : 0;
        $naziv_stavke = strlen($stavka['naziv_stavke']) > 100 ? substr($stavka['naziv_stavke'], 0 , 100) . "..." : $stavka['naziv_stavke'];

        $selected_pdv = "";
        if($pdv_sifra == $val_drop) {
            $selected_pdv = "selected='selected'";
        }

        echo "<option ".$selected_pdv." ima-pdv='".$ima_pdv."' title='".$stavka['naziv_stavke']."' pun-naziv='".$stavka['naziv_stavke']."' value='" .$val_drop . "'>" . $broj_stavke . " - " . $pdv_prikaz . $naziv_stavke   . "</option>";
    }

    echo "</optgroup>";

}
echo "</select>";

echo "</td>\n";
echo "</tr>\n";

list($br_ev, $br_stavke, $pdv_val) = explode('||', $pdv_sifra);
if(!empty($pdv_val)) {
    echo "<tr id='prikazi-ako-ima-pdv'>";
}
else {
    echo "<tr id='prikazi-ako-ima-pdv' style='display: none;'>";
}


echo "<td align=\"right\">";
echo "Da li se iznos PDV-a može odbiti? : ";
echo "</td>\n<td";
echo " colspan=\"3\"";
echo ">";

echo "<select name=\"pdv_moze_da_se_odbije\">\n";
echo "<option value='0' selected='selected'>Ne</option>";
echo "<option value='1'>Da</option>";
echo "</select>";

echo "</tr>";

// dodato 2018-11-20 select lista za odabir PDV-a - POCETAK


echo "<tr>\n";

echo "<td colspan=\"4\">&nbsp;</td></tr>\n";

echo "<tr><td align=\"center\" colspan=\"4\">";
echo "<input type=\"submit\" value=\"Pošalji\" class=\"button\" name=\"submit\">\n";
	echo "<input type='button' value='Predlog knjiženja' id='predlog'>";
echo "&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Proveri\" class=\"button\" name=\"submit\">\n";
echo "&nbsp;&nbsp;&nbsp;<input type=\"reset\" value=\"Poništi\" class=\"button\" name=\"reset\">\n";
echo "&nbsp;&nbsp;&nbsp;<font color=\"red\">* Radnik je: <b>$radnik</b></font></p></td></tr>\n";

// echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";

// Obrada (zp)

if (isset($submit) && $submit == 'Proveri') {
$mozda = 1;
if (!je_datum($datknjiz) || substr($datknjiz,0,4) != $godina) {
echo "<script language=\"javascript\">\n";
// echo "document.knjizenje.datknjiz.value='';\n";
echo "alert(\"Neispravan datum knjiženja!\")\n";
echo "document.knjizenje.datknjiz.focus();\n";
echo "</script>\n";
$mozda = 0;
}

$brojnaloga = preg_replace("/\ {1,}/",'',$brojnaloga);
if (!$brojnaloga && $mozda) {
echo "<script language=\"javascript\">\n";
echo "document.knjizenje.brojnaloga.value='';\n";
echo "alert(\"Broj naloga ne sme biti prazan!\")\n";
echo "document.knjizenje.brojnaloga.focus();\n";
echo "</script>\n";
$mozda = 0;
}

if ($mozda) {
$sql = "select count(*) as ima_ga from g" . $godina . " where vrstadok='$vrstadok' and brdok='$brojnaloga' and datknjiz = '$datknjiz'::date";
$result=pg_query($conn, $sql);
$arr = pg_fetch_assoc($result);
if ($arr['ima_ga']) {
  echo "<script language=\"javascript\">\n";
  echo "alert(\"Ovaj nalog postoji!\")\n";
  echo "document.knjizenje.brojnaloga.focus();\n";
  echo "</script>\n";
$mozda = 0;
}
}

if ($mozda) {
$sql = "select count(*) as ima_ga from g" . $godina . " where vrstadok='$vrstadok' and brdok='$brojnaloga' and datknjiz != '$datknjiz'::date";
$result=pg_query($conn, $sql);
$arr = pg_fetch_assoc($result);
if ($arr['ima_ga']) {
echo "<script language=\"javascript\">\n";
echo "alert(\"Postoji nalog sa istim brojem, a drugim datumom!\")\n";
echo "document.knjizenje.brojnaloga.focus();\n";
echo "</script>\n";
  }
}

}

if (isset($submit) && $submit == 'Pošalji') {

$da = 1;

if ($datknjiz < $zakljucano) {
echo "<script language=\"javascript\">\n";
// echo "document.knjizenje.datknjiz.value='';\n";
echo "alert(\"U ovom periodu se više ne može knjižiti!\")\n";
echo "document.knjizenje.datknjiz.focus();\n";
echo "</script>\n";
$da = 0;
}

if (!je_datum($datknjiz) || $datknjiz > date("Y-m-d")) {
echo "<script language=\"javascript\">\n";
// echo "document.knjizenje.datknjiz.value='';\n";
echo "alert(\"Neispravan datum knjiženja!\")\n";
echo "document.knjizenje.datknjiz.focus();\n";
echo "</script>\n";
$da = 0;
}

$brojnaloga = preg_replace("/\ {1,}/",'',$brojnaloga);
if (!$brojnaloga && $da) {
echo "<script language=\"javascript\">\n";
echo "document.knjizenje.brojnaloga.value='';\n";
echo "alert(\"Broj naloga ne sme biti prazan!\")\n";
echo "document.knjizenje.brojnaloga.focus();\n";
echo "</script>\n";
$da = 0;
}

if ($konto && $da && $submit != 'Konto') {
$sql="SELECT konto, anasin, slovon FROM $k_plan WHERE konto='$konto'";
$result=pg_query($conn, $sql);
if (!$result) {
    echo "Greška u određivanju broja relevantnih zapisa.\n";
    exit;
}

// echo $sql;

$redova = pg_num_rows($result);
  if (!$redova) {
  echo "<script language=\"javascript\">\n";
  echo "document.knjizenje.konto.value='';\n";
  echo "alert(\"Ovaj konto ne postoji!\")\n";
  echo "document.knjizenje.konto.focus();\n";
  echo "</script>\n";
  $da = 0;
  }
  else {
  $arr = pg_fetch_assoc($result);
  $anasin = $arr['anasin'];
  $slovon = $arr['slovon'];
  if ($slovon == 'N') {
    echo "<script language=\"javascript\">\n";
    echo "document.knjizenje.konto.value='';\n";
    echo "alert(\"Na ovaj konto se ne može knjižiti!\")\n";
    echo "document.knjizenje.konto.focus();\n";
    echo "</script>\n";
    $da = 0;
    }
  if ($anasin == 'A' && $partner) {
    echo "<script language=\"javascript\">\n";
//    echo "document.knjizenje.konto.value='';\n";
    echo "document.knjizenje.partner.value='';\n";
    echo "alert(\"Ne može se knjižiti na partnera na analitičkom kontu!\")\n";
    $naziv = '';
    echo "";
    echo "document.knjizenje.konto.focus();\n";
    echo "</script>\n";
    $da = 0;
    }
  if ($anasin == 'S' && !$partner) {
    echo "<script language=\"javascript\">\n";
    echo "alert(\"Na ovom kontu se mora knjižiti na partnera!\")\n";
    echo "document.knjizenje.partner.focus();\n";
    echo "</script>\n";
    $da = 0;
    }
  }
  
}
else {
if ($da && $submit != 'Konto') {
echo "<script language=\"javascript\">\n";
echo "alert(\"Obavezno se mora uneti konto!\")\n";
echo "document.knjizenje.konto.focus();\n";
echo "</script>\n";
$da = 0;
}
}


// dodato 2016-01-29 - POCETAK izmenjeno 2016-03-14 POCETAK
$konto_prve_cifre = substr ( $konto , 0, 3);
$konto_prve_cifre_dodatno = $konto_prve_cifre . '3';

if($konto_prve_cifre == '610')
{
	if(strlen($partner) == 13)
	{
		$konto_dva_osma_cifra = '2';
	}
	else if(strlen($partner) == 9)
    {
		$konto_dva_osma_cifra = '1';
	}

	$konto_cetiri_poseldnje_dve_cifre = substr($konto, 3,2);
	$konto_2 = '201' . $konto_cetiri_cifre_iz_sestice;
}
else if($konto_prve_cifre == '472')
{
	$konto_vrsta_osiguranja = substr($konto, 3);

	$konto_2 = '201' . $konto_vrsta_osiguranja;
}


if(($konto_prve_cifre == '610' && $potrazuje != 0) || ($konto_prve_cifre == '472' && $potrazuje != 0))
{
	
	if(strlen($partner) == 13)
	{
		$konto_dva_osma_cifra = '2';
	}
	else if(strlen($partner) == 9)
	{
		$konto_dva_osma_cifra = '1';
	}
	
	
	if($konto == '6101201' || $konto == '6101202')
	{
		$konto_2 = '20112';
	}
	else if(substr($konto, 0,5) == '61001')
	{
		$konto_2 = '2010101' . $konto_dva_osma_cifra;
	}
	else
	{
		$konto_2 = ($konto_prve_cifre == '610' && $konto != '6101201') ? '201' . substr($konto, 3, 4): '201' . substr($konto, 3);
	}
	//$sql_provera_dugovne_dvojke  = " SELECT * FROM g$godina WHERE brojdok='$brojdok' AND duguje !=0 AND konto ILIKE '$konto_2%' LIMIT 1";
	//$sql_provera_dugovne_dvojke  = " SELECT sum(duguje), brojdok, pib FROM g$godina WHERE brojdok='$brojdok' AND konto ILIKE '$konto_2%' GROUP BY brojdok,pib HAVING sum(duguje) > 0";
	$sql_provera_dugovne_dvojke  = " SELECT sum(duguje), brojdok, pib FROM g$godina WHERE  brojdok='$brojdok' AND konto ILIKE '$konto_2%' GROUP BY brojdok,pib HAVING sum(duguje) != 0";
	
	
	$result_provera_dugovne_dvojke = pg_query ($conn, $sql_provera_dugovne_dvojke);
	$arr_provera_dugovne_dvojke = pg_fetch_assoc($result_provera_dugovne_dvojke);
	$kanal_prodaje = $arr_provera_dugovne_dvojke['pib'];
	if(!$kanal_prodaje)
	{
	echo "<script language=\"javascript\">\n";
			echo "alert(\"Nije moguće uneti promenu, ne postoji dugovna dvojka!\")\n";
			echo "document.promena.datdok.focus();\n";
			echo "</script>\n";
			$da = 0;
		}
	}

if($konto_prve_cifre == '201' && $potrazuje != 0 )
{
	if(substr($konto, 0, 4) == '2019')
	{
		$kanal_prodaje = 1;
	}
	else 
	{
		//$sql_provera_dugovne_dvojke  = " SELECT * FROM g$godina WHERE partner = '$partner' AND brojdok='$brojdok' AND duguje !=0 AND konto='$konto' LIMIT 1";
		//$sql_provera_dugovne_dvojke  = "SELECT sum(duguje),brojdok,pib FROM g$godina WHERE partner = '$partner' AND brojdok='$brojdok' AND konto ILIKE '$konto' GROUP BY brojdok,pib HAVING sum(duguje) != 0";
		$sql_provera_dugovne_dvojke  = "SELECT brojdok,pib FROM g$godina WHERE partner = '$partner' AND brojdok='$brojdok' AND konto ILIKE '$konto' AND duguje != 0 GROUP BY brojdok,pib";
		//$sql_provera_dugovne_dvojke  = "SELECT sum(duguje),brojdok,pib FROM g$godina WHERE partner = '$partner' AND brojdok='$brojdok' AND konto ILIKE '$konto' GROUP BY brojdok,pib HAVING sum(duguje) > 0";
		$result_provera_dugovne_dvojke = pg_query ($conn, $sql_provera_dugovne_dvojke);
		$arr_provera_dugovne_dvojke = pg_fetch_assoc($result_provera_dugovne_dvojke);
		$kanal_prodaje = $arr_provera_dugovne_dvojke['pib'];
		if(!$kanal_prodaje)
		{
			echo "<script language=\"javascript\">\n";
			echo "alert(\"Nije moguće uneti promenu, ne postoji dugovna dvojka!\")\n";
			echo "document.promena.datdok.focus();\n";
			echo "</script>\n";
			$da = 0;
		}
	}
}

if(substr($konto, 0, 4) == '2093' && $duguje != 0 )
{
	//$sql_provera_potrazna_dvojke = " SELECT * FROM g$godina WHERE partner = '$partner' AND brojdok='$brojdok' AND potrazuje !=0 AND konto='$konto' LIMIT 1";
	$sql_provera_potrazna_dvojke = "SELECT sum(potrazuje),brojdok,pib FROM g$godina WHERE partner = '$partner' AND brojdok='$brojdok' AND konto ILIKE '$konto' GROUP BY brojdok,pib HAVING sum(potrazuje) != 0";
	$result_provera_potrazna_dvojke = pg_query ($conn, $sql_provera_potrazna_dvojke);
	$arr_provera_potrazna_dvojke = pg_fetch_assoc($result_provera_potrazna_dvojke);
	$kanal_prodaje = $arr_provera_potrazna_dvojke['pib'];
	if(!$kanal_prodaje)
	{
		echo "<script language=\"javascript\">\n";
		echo "alert(\"Nije moguće uneti promenu, ne postoji potrazna dvojka!\")\n";
		echo "document.promena.datdok.focus();\n";
		echo "</script>\n";
		$da = 0;
	}
} 

if((($konto_prve_cifre == '201' && $duguje != 0) || (substr($konto, 0, 4) == '2093' && $potrazuje != 0) || ($konto_prve_cifre == '472' && $duguje != 0) || $konto_prve_cifre == '613') && $kanal_prodaje == 0)
{
	echo "<script language=\"javascript\">\n";
	echo "alert(\"Morate uneti kanal prodaje!\")\n";
	echo "document.knjizenje.kanal_prodaje.focus();\n";
	echo "</script>\n";
	$da = 0;
}

if($pdv_sifra && $pdv_sifra < 0) {
    echo "<script language=\"javascript\">\n";
    echo "alert(\"Morate izabrati POPDV šifru!\")\n";
    echo "document.knjizenje.pdv_sifra.focus();\n";
    echo "</script>\n";
    $da = 0;
}

// 2016-03-14 KRAJ

// vrednost koja se upisuje u glanu knjigu u kolonu pib 2016-02-19
//$kanal_prodaje;
$kanal_prodaje = ($kanal_prodaje == 0 || ($kanal_prodaje != 0 && $konto_prve_cifre != '201' && $konto_prve_cifre !='472' && $konto_prve_cifre_dodatno != '2093' && $konto_prve_cifre !='610' && $konto_prve_cifre !='613')) ? 'null': $kanal_prodaje;

// dodato 2016-01-29 - KRAJ


if ($partner) {
$sql="SELECT count(*) AS total FROM partneri WHERE sifra='$partner'";
$result=pg_query($conn, $sql);
if (!$result) {
    echo "Greška u određivanju broja relevantnih zapisa o partnerima.\n";
    exit;
}
$arr = pg_fetch_assoc($result);
$broj = $arr['total'];
if (!$broj) {

if (ereg("[1-2][0-9]{8,8}", $partner)) {
$konn = pg_connect ("host=localhost dbname=jpc user=zoranp");
if (!$conn) {
	echo "Greška otvaranja konekcije prema SQL serveru.";
	exit;
	}

$sql = "SELECT pib, naziv, ulica || ' ' || broj AS adresa, posbroj, mesto FROM (select matbr, pib, naziv, mesto, opstina, posbroj, ulica, broj, sifdel, pdv from pravna UNION ALL select * from str) AS b where pib = '$partner' ";
$result = pg_query($konn, $sql);

$arr = pg_fetch_row($result);
$n_pib = $arr[0];
$n_naziv = substr($arr[1], 0, 40);
$n_adresa = substr($arr[2], 0, 30);
$n_posbroj = $arr[3];
$n_mesto = substr($arr[4], 0, 20);

pg_close($konn);

if ($n_pib == $partner) {
$upit = "INSERT INTO partneri (sifra, naziv, adresa, posbroj, mesto) VALUES ('$n_pib', '$n_naziv', '$n_adresa', $n_posbroj, '$n_mesto')";
$rez = pg_query($conn, $upit);
if ($rez) {
echo "<script language=\"javascript\">\n";
echo "alert(\"Podaci o partneru uspešno upisani!\")\n";
echo "</script>\n";
}
else {
echo "<script language=\"javascript\">\n";
echo "alert(\"Podaci o partneru se ne mogu upisati!\")\n";
echo "</script>\n";
$da = 0;
}
}
else {
echo "<script language=\"javascript\">\n";
echo "alert(\"Ovaj partner ne postoji u registru pravnih lica!\")\n";
echo "</script>\n";
$da = 0;
}

}
else {
echo "<script language=\"javascript\">\n";
echo "document.knjizenje.partner.value='';\n";
echo "alert(\"Ovaj partner ne postoji!\")\n";
echo "document.knjizenje.partner.focus();\n";
echo "</script>\n";
$da = 0;
}
}
}

$opisdok = preg_replace("/^\ {1,}/",'',$opisdok);
$opisdok = preg_replace("/\ {1,}$/",'',$opisdok);
if ((!$opisdok) && $da) {
echo "<script language=\"javascript\">\n";
// echo "document.knjizenje.opisdok.value='';\n";
echo "alert(\"Opis dokumenta ne može biti prazan!\")\n";
echo "document.knjizenje.opisdok.focus();\n";
echo "</script>\n";
$da = 0;
}

$brojdok = preg_replace("/^\ {1,}/",'',$brojdok);
$brojdok = preg_replace("/\ {1,}$/",'',$brojdok);
if (substr($datknjiz, 0, 4) > 2013) {
	if ((!$brojdok || strlen($brojdok)>20) && $da) {
		echo "<script language=\"javascript\">\n";
		// echo "document.knjizenje.brojdok.value='';\n";
		echo "alert(\"Broj dokumenta ne može biti prazan ili duži od 20 karaktera!\")\n";
		echo "document.knjizenje.brojdok.focus();\n";
		echo "</script>\n";
		$da = 0;
	}
}
else {
	if ((!$brojdok || strlen($brojdok)>12) && $da) {
		echo "<script language=\"javascript\">\n";
		// echo "document.knjizenje.brojdok.value='';\n";
		echo "alert(\"Broj dokumenta ne može biti prazan ili duži od 12 karaktera!\")\n";
		echo "document.knjizenje.brojdok.focus();\n";
		echo "</script>\n";
		$da = 0;
	}
}

// if (!je_datum($datdok) || $datdok > date("Y-m-d") || $datdok > $datknjiz && $da) {

if (!je_datum($datdok) && $da) {
echo "<script language=\"javascript\">\n";
echo "document.knjizenje.datdok.value='';\n";
echo "alert(\"Neispravan datum dokumenta!\")\n";
echo "document.knjizenje.datdok.focus();\n";
echo "</script>\n";
$da = 0;
}

// Mogu samo Biljana i Jovana
if ($radnik != 2012 && $radnik != 2223) {
if ($datdok > date("Y-m-d") && $da) {
echo "<script language=\"javascript\">\n";
echo "document.knjizenje.datdok.value='';\n";
echo "alert(\"Neispravan datum dokumenta!\")\n";
echo "document.knjizenje.datdok.focus();\n";
echo "</script>\n";
$da = 0;
}
}


if (!ereg("^[\-]?[0-9]{1,12}\.?[0-9]{0,2}$",$duguje)){
echo "<script language=\"javascript\">\n";
echo "document.knjizenje.duguje.value='0.0';\n";
echo "alert(\"Broj može sadržavati isključivo CIFRE i DECIMALNU TAČKU!\")\n";
echo "document.knjizenje.duguje.focus();\n";
echo "</script>\n";
$da = 0;
}


if (!ereg("^[\-]?[0-9]{1,12}\.?[0-9]{0,2}$",$potrazuje)){
echo "<script language=\"javascript\">\n";
echo "document.knjizenje.potrazuje.value='0.0';\n";
echo "alert(\"Broj može sadržavati isključivo CIFRE i DECIMALNU TAČKU!\")\n";
echo "document.knjizenje.potrazuje.focus();\n";
echo "</script>\n";
$da = 0;
}


if ($brojnaloga and strlen($brojnaloga) > 6 and $da) {
echo "<script language=\"javascript\">\n";
echo "document.knjizenje.brojnaloga.value='';\n";
echo "alert(\"Broj naloga može imati maksimalno 6 karaktera!\")\n";
echo "document.knjizenje.brojnaloga.focus();\n";
echo "</script>\n";
$da = 0;
}


if ($brojdok && strlen($brojdok) > 12 && substr($datknjiz, 0, 4) < 2014 && $da) {
echo "<script language=\"javascript\">\n";
echo "document.knjizenje.brojdok.value='';\n";
echo "alert(\"Broj dokumenta može imati maksimalno 12 karaktera!\")\n";
echo "document.knjizenje.brojdok.focus();\n";
echo "</script>\n";
$da = 0;
}


if (!$opisdok && $da) {
echo "<script language=\"javascript\">\n";
echo "document.knjizenje.opisdok.value='';\n";
echo "alert(\"Broj naloga se ne može izostaviti!\")\n";
echo "document.knjizenje.opisdok.focus();\n";
echo "</script>\n";
$da = 0;
}


if ($duguje == 0 && $potrazuje == 0) {
  $da = 0;
}

if ($duguje != 0 && $potrazuje != 0 && $da) {
echo "<script language=\"javascript\">\n";
echo "document.knjizenje.duguje.value='0.0';\n";
echo "document.knjizenje.potrazuje.value='0.0';\n";
echo "alert(\"Jedna stavka se ne može knjižiti na obe strane naloga istovremeno!\")\n";
echo "document.knjizenje.duguje.focus();\n";
echo "</script>\n";
$da = 0;
}

if (!$dospelo) {
$dospelo = 0;
}
else {
if (!ereg("^[0-9]{1,3}$",$dospelo)){
echo "<script language=\"javascript\">\n";
echo "document.knjizenje.dospelo.value='0';\n";
echo "alert(\"Broj dana može sadržavati isključivo CIFRE!\")\n";
echo "document.knjizenje.dospelo.focus();\n";
echo "</script>\n";
$da = 0;
}
}


if( (in_array ($konto, array('2010110', '2010120', '2010310','2010320', '2010710', '2010720','2010810', '2010820', '2010910', '2010920', '20110020', '2011310', '2011320', '2011410', '2011420','2011510','2011520', '2011610', '2011620','2011710','2011720','2011810','2011820','2181840','2181850') )) and $potrazuje<>0 and $da){
echo "<script language=\"javascript\">\n";
echo "document.knjizenje.konto.value='0.0';\n";
echo "alert(\"Nije dozvoljeno knjiženje potražne strane na konto za nedospela potraživanja !\")\n";
echo "document.knjizenje.konto.focus();\n";
echo "</script>\n";

$da = 0;
}

   if ($da) {

		$upit = "BEGIN";
		$rez = pg_query($conn, $upit);
		$rez = pg_query($conn1, $upit);

    if ($vrstadok=='OS' &&  in_array ($konto,array('0101','0109','01300','0141','0142','0149','0221','02291','02310','023110','02312','02313','02314','02315','02316','02317','02318','02319','0239','023910','023911','023912','023913','023914','023915','023916','023917','023918','023919','023999','0241','0261','02610','02620','02691','02692','0290','0291','0263','0264', '02411', '02412' , '0222', '0223', '02000'))){

    $niz = explode("-", $datdok);

    if ($niz[1]=='12'){$god=$niz[0]+1; $mesec='01';}
                  else{$mesec=$niz[1]+1; $god=$niz[0];}

    $mesec=sprintf("%02d",$mesec);

    $datakt=$god . '-' . $mesec . '-01';

     if(!ereg("^[A-Za-zšđčćžČĆŽĐŠ /.]*$",$brojdok)){


     $sql="select invbr from osnovna where invbr=$brojdok";
     $rezultat=pg_query($conn1,$sql);
     $niz=pg_fetch_assoc($rezultat);
     $bros= $niz['invbr'];

     if (!$bros){
     echo "<script language=\"javascript\">\n";
     echo "document.knjizenje.brojdok.value='';\n";
     echo "alert(\"Nepostojeći inventarni broj!\")\n";
     echo "document.knjizenje.brojdok.focus();\n";
     echo "</script>\n";
     $da = 0;
     }


     $sql="insert into ulaganja (invbr,naziv, partner , konto, datnab, datakt, nabavna, trenutna, mnt, vrsta, radnik, dana, vreme ) values ($brojdok,'$opisdok', '$partner', '$konto', '$datdok', '$datakt', $duguje, $duguje, '$mnt','U', $radnik, current_date, current_time) ";
     $result1=pg_query($conn1, $sql);

    $invbr=$brojdok;
    $vr='U';
    }
    else{

    if($konto=='01300' || $konto=='0141' || $konto=='0142' || $konto=='0290' || $konto=='0291'){$sqlp="select  max(invbr) as invbr  from osnovna where invbr>100000";}
                                    else{$sqlp="select  max(invbr) as invbr  from osnovna where invbr<100000";}


    $resultp=pg_query($conn1, $sqlp);
    $arrp = pg_fetch_assoc ($resultp);
    $invbr = $arrp['invbr'];

    if(!$invbr){
    if ($konto=='01300' || $konto=='0141' || $konto=='0142' || $konto=='0290' || $konto=='0291'){$invbr=100001;}
                                                                         else{$invbr=1;}
               }
           else{$invbr=$invbr+1;}

    $sql="insert into osnovna (invbr,naziv, partner , konto, datnab, datakt, nabavna, trenutna, mnt, radnik, dana, vreme ) values ($invbr,'$opisdok', '$partner', '$konto', '$datdok', '$datakt', $duguje, $duguje, '$mnt', $radnik, current_date, current_time) ";
     $result1=pg_query($conn1, $sql);

     $brojdok=$invbr;
     $vr='N';
    }


    }

  /* Uklonjen deo koji je popunjavao tabelu uplate_ak
   * Ako je $result1 prethodno definisan, zadržaće svoju vrednost, a ako nije
   * onda će dobiti vrednost true, koja se proverava malo kasnije u skriptu...
   */
	$result1 = isset($result1) ? $result1 : true;

   $gpartner = $partner;
   $radni = (int) $radnik;
   $sql = "INSERT INTO g" . $godina . " (datknjiz, vrstadok, brdok, ff, partner, pib, ppsi, opisdok, brojdok, datdok, duguje, potrazuje, ";
   $sql .= "opetnalog, konto, dospeva, mnt, radnik, knjizdana, vremknjiz) VALUES ('$datknjiz'::date, '$vrstadok', '$brojnaloga', ";
   if ($anasin == 'A') {
   $sql .= "'F', NULL, $kanal_prodaje,'SI', '$opisdok', '$brojdok', '$datdok'::date, $duguje, $potrazuje, '$brojnaloga', ";
   }
   else {
   $sql .= "'F', '$gpartner', $kanal_prodaje, 'PP', '$opisdok', '$brojdok', '$datdok'::date, $duguje, $potrazuje, '$brojnaloga', ";
   }
//   if (!$dospelo){$sql .= "'$konto', '$datdok', '$mnt', $radnik, current_date, current_time)";}
//             else{$sql .= "'$konto', '$dospelo', '$mnt', $radnik, current_date, current_time)";}
   $sql .= "'$konto', '$datdok'::date + interval '$dospelo days', '$mnt', $radnik, current_date, current_time) RETURNING brknjiz";
   $result=pg_query($conn, $sql);

   //kod koji hendla upisivanje pdv sifre u vezivnu tabelu POCETAK

   //vracamo poslednji brknjiz da bi mogli da iskoristimo u unosu za PDV
   $insert_row = pg_fetch_row($result);
   $brknjiz = $insert_row[0];

   //setujemo status inserta za PDV, da uradimo rollback ukoliko dodje do greske neke
   $upis_pdv_status = true;

   //proverimo gde je iznos veci od nule, pa uzmimao ili duguje ili potrazuje
   if($duguje > 0) {
       $cifra = $duguje;
   }
   else {
       $cifra = $potrazuje;
   }

   //definisemo tabelu za knjizenje i definisemo id knjizenja
   $tabela_knjiznja = "g" . $godina;
   $id_knjizenja = $brknjiz;

   //ukoliko je pdv_sifra razlicita od nule, znaci da je izabran neki PDV
   if($pdv_sifra != 0) {

       //razbijemo pdv sifru da znamo koja je stavka, koja evidencija i koji pdv
       list($br_ev, $br_stavke, $pdv_val) = explode('||', $pdv_sifra);

       //ako je vrednost pdv-a nije prazna, znaci da imamo pdv ili 10 ili 20 posto
       if(!empty($pdv_val)) {

           //racunamo osnovicu i racunamo pdv iznose da bi upisali
           $osnovica_cifra = round(($cifra * 100) / (100 + $pdv_val), 2);
           $pdv_cifra = round(($cifra * $pdv_val) / (100 + $pdv_val), 2);


           //ukoliko je stavka 3a.3. to znaci da ne treba da racunamo pdv i osnovicu, jer je cifra uneta ustvari PDV
           if($br_ev == "3a") {


               //trazimo pdv sifru za osnovicu
               $sql_osnovica = "
                    SELECT
                        *
                    FROM
                        sifarnici.pdv_sifarnik
                    WHERE
                        broj_evidencije = '".$br_ev."'
                        AND broj_stavke = '".$br_stavke."'
                        AND pdv = '".$pdv_val."'
                        AND tip_iznosa = 1;
                    ";

               $rez_osnovica = pg_query($conn,$sql_osnovica);
               $niz_osnovica = pg_fetch_assoc($rez_osnovica);
               $id_osnovica = $niz_osnovica['id'];

               //upisujemo osnovicu u vezivnu tabelu
               $insert_osnovica_sql = "INSERT INTO pdv_knjizenje (tabela_knjizenja, id_knjizenja, id_pdv_sifre, pdv_moze_da_se_odbije, iznos) VALUES ('$tabela_knjiznja', $id_knjizenja, $id_osnovica, 'false', 0)";
               $rez_insert_osnovica = pg_query($conn, $insert_osnovica_sql);

               //ukoliko je doslo do neke greske, stavljamo status na false, da bi radili rollback
               if(!$rez_insert_osnovica) {
                   $upis_pdv_status = false;
               }


               //selektujemo id pdv sifre za pdv
               $sql_pdv = "
                SELECT
                    *
                FROM
                    sifarnici.pdv_sifarnik
                WHERE
                    broj_evidencije = '".$br_ev."'
                    AND broj_stavke = '".$br_stavke."'
                    AND pdv = '".$pdv_val."'
                    AND tip_iznosa = 2;
                ";

                   $rez_pdv = pg_query($conn,$sql_pdv);
                   $niz_pdv = pg_fetch_assoc($rez_pdv);
                   $id_pdv = $niz_pdv['id'];

                   $pdv_moze_da_se_odbije = $pdv_moze_da_se_odbije ? 'true' : 'false';

                   //insertujemo red u vezivnu tabelu za pdv
                   $insert_pdv_sql = "INSERT INTO pdv_knjizenje (tabela_knjizenja, id_knjizenja, id_pdv_sifre, pdv_moze_da_se_odbije, iznos) VALUES ('$tabela_knjiznja', $id_knjizenja, $id_pdv, $pdv_moze_da_se_odbije, $cifra)";
                   $rez_insert_pdv = pg_query($conn, $insert_pdv_sql);

                   //ukoliko je doslo do greske stavljamo status na false da bi uradili rollback
                   if(!$rez_insert_pdv) {
                       $upis_pdv_status = false;
                   }

           }
           //ukoliko je stavka 8.g.1, to je samo osnovica i ne razbijamo nista nego knjizimo ovako kako jeste
           else if($br_stavke == "8.g.1." || $br_stavke == "8.b.2.") {

               //trazimo pdv sifru za osnovicu
               $sql_osnovica = "
                    SELECT
                        *
                    FROM
                        sifarnici.pdv_sifarnik
                    WHERE
                        broj_evidencije = '".$br_ev."'
                        AND broj_stavke = '".$br_stavke."'
                        AND pdv = '".$pdv_val."'
                        AND tip_iznosa = 1;
                    ";

                       $rez_osnovica = pg_query($conn,$sql_osnovica);
                       $niz_osnovica = pg_fetch_assoc($rez_osnovica);
                       $id_osnovica = $niz_osnovica['id'];

                       //upisujemo osnovicu u vezivnu tabelu
                       $insert_osnovica_sql = "INSERT INTO pdv_knjizenje (tabela_knjizenja, id_knjizenja, id_pdv_sifre, pdv_moze_da_se_odbije, iznos) VALUES ('$tabela_knjiznja', $id_knjizenja, $id_osnovica, 'false', $cifra)";
                       $rez_insert_osnovica = pg_query($conn, $insert_osnovica_sql);

                       //ukoliko je doslo do neke greske, stavljamo status na false, da bi radili rollback
                       if(!$rez_insert_osnovica) {
                           $upis_pdv_status = false;
                       }

           }
           //ostali slucajevi treba da rade kako je definisano
           else {
               //trazimo pdv sifru za osnovicu
               $sql_osnovica = "
                SELECT
                    *
                FROM
                    sifarnici.pdv_sifarnik
                WHERE
                    broj_evidencije = '".$br_ev."'
                    AND broj_stavke = '".$br_stavke."'
                    AND pdv = '".$pdv_val."'
                    AND tip_iznosa = 1;
                ";

               $rez_osnovica = pg_query($conn,$sql_osnovica);
               $niz_osnovica = pg_fetch_assoc($rez_osnovica);
               $id_osnovica = $niz_osnovica['id'];

               //selektujemo id pdv sifre za pdv
               $sql_pdv = "
                SELECT
                    *
                FROM
                    sifarnici.pdv_sifarnik
                WHERE
                    broj_evidencije = '".$br_ev."'
                    AND broj_stavke = '".$br_stavke."'
                    AND pdv = '".$pdv_val."'
                    AND tip_iznosa = 2;
                ";

               $rez_pdv = pg_query($conn,$sql_pdv);
               $niz_pdv = pg_fetch_assoc($rez_pdv);
               if(!$niz_pdv) {
                   $osnovica_cifra = $cifra;
               }

               //upisujemo osnovicu u vezivnu tabelu
               $insert_osnovica_sql = "INSERT INTO pdv_knjizenje (tabela_knjizenja, id_knjizenja, id_pdv_sifre, pdv_moze_da_se_odbije, iznos) VALUES ('$tabela_knjiznja', $id_knjizenja, $id_osnovica, 'false', $osnovica_cifra)";
               $rez_insert_osnovica = pg_query($conn, $insert_osnovica_sql);

               //ukoliko je doslo do neke greske, stavljamo status na false, da bi radili rollback
               if(!$rez_insert_osnovica) {
                   $upis_pdv_status = false;
               }


               if($niz_pdv) {

                   $id_pdv = $niz_pdv['id'];
                   $pdv_moze_da_se_odbije = $pdv_moze_da_se_odbije ? 'true' : 'false';

                   //insertujemo red u vezivnu tabelu za pdv
                   $insert_pdv_sql = "INSERT INTO pdv_knjizenje (tabela_knjizenja, id_knjizenja, id_pdv_sifre, pdv_moze_da_se_odbije, iznos) VALUES ('$tabela_knjiznja', $id_knjizenja, $id_pdv, $pdv_moze_da_se_odbije, $pdv_cifra)";
                   $rez_insert_pdv = pg_query($conn, $insert_pdv_sql);

                   //ukoliko je doslo do greske stavljamo status na false da bi uradili rollback
                   if (!$rez_insert_pdv) {
                       $upis_pdv_status = false;
                   }
               }
           }

       }
       else {
        // ukoliko pdv sifra nema odabran pdv, onda samo unsimo osnovicu i ne racunamo nista

           //nadjemo sifru osnovice za evidenciju i stavku
           $sql = "
            SELECT
                *
            FROM
                sifarnici.pdv_sifarnik
            WHERE
                broj_evidencije = '".$br_ev."'
                AND broj_stavke = '".$br_stavke."'
                AND pdv is null
                AND tip_iznosa = 1;
            ";

           $rez_osnovica = pg_query($conn,$sql);
           $niz_osnovica = pg_fetch_assoc($rez_osnovica);
           $id_osnovica = $niz_osnovica['id'];

           //insertujemo red u vezivnu tabelu za osnovicu
           $insert_osnovica_sql = "INSERT INTO pdv_knjizenje (tabela_knjizenja, id_knjizenja, id_pdv_sifre, pdv_moze_da_se_odbije, iznos) VALUES ('$tabela_knjiznja', $id_knjizenja, $id_osnovica, 'false', $cifra)";
           $rez_insert_osnovica = pg_query($conn, $insert_osnovica_sql);

           //ako je doslo do greske, onda uzmemo i stavimo status na false da bi uradili rollback
           if(!$rez_insert_osnovica) {
               $upis_pdv_status = false;
           }
       }
   }
   else {

       //ovde upisujemo samo ukoliko nije nista odabrano, da cisto imamo i to da je ubaceno kao knjizeno
       $insert_osnovica_sql = "INSERT INTO pdv_knjizenje (tabela_knjizenja, id_knjizenja, iznos) VALUES ('$tabela_knjiznja', $id_knjizenja, $cifra)";
       $rez_insert_osnovica = pg_query($conn, $insert_osnovica_sql);

       if(!$rez_insert_osnovica) {
           $upis_pdv_status = false;
       }
   }
   //kod koji hendla upisivanje pdv sifre u vezivnu tabelu KRAJ


// echo $sql;

   if (!$result || !$result1 || !$upis_pdv_status) {
    echo "Greška prilikom upisa.\n";
    $upit = "ROLLBACK";
    $rez = pg_query($conn, $upit);
    $rez = pg_query($conn1, $upit);
    exit;
     }
     else {

			 $upit = "COMMIT";
			 $rez = pg_query($conn, $upit);
			 $rez = pg_query($conn1, $upit);

      //$koja=array('0101','0109');

      if ($vrstadok=='OS' && in_array ($konto,array('0101','0109','01300','0141','0142','0149','0221','02291','02310','023110','02312','02313','02314','02315','02316','02317','02318','02319','0239','023910','023911','023912','023913','023914','023915','023916','023917','023918','023919','023999','0241','0261','02610','02620','02691','02692','0290','0291', '0263', '0264', '02411', '02412', '0222', '0223', '02000'))) {
       echo "<script language=\"javascript\">\n";
       echo "alert(\"Podaci uspešno upisani!\")\n";

       echo "window.close();\n";
       echo "window.open ('../../evidencije/osnovna/unos.php?invbr=$invbr&vr=$vr', 'main')\n";
       echo "</script>\n";
       exit();
     }
     else {
       echo "<script language=\"javascript\">\n";
       echo "alert(\"Podaci uspešno upisani!\");\n";
//     echo "alert($sql)\n";
       echo "window.open ('unos.php?datknjiz=$datknjiz&datdok=$datdok&brojnaloga=$brojnaloga&vrstadok=$vrstadok&partner=$partner&opisdok=$opisdok&brojdok=$brojdok', 'main')\n";
       echo "</script>\n";
       exit();
     }
     }
   }


}

// Kontrola OK (zp)

$sql="SELECT sum(duguje) AS dug, sum(potrazuje) AS trazi, sum(duguje-potrazuje) AS saldo FROM g" . $godina . " ";
$sql .= "WHERE brdok='$brojnaloga' AND vrstadok='$vrstadok' ";
if (je_datum($datknjiz)) {
$sql .= "AND datknjiz='$datknjiz'::date";
}
$result=pg_query($conn, $sql);
if (!$result) {
    echo "Greška u određivanju broja relevantnih zapisa.\n";
    exit;
}
$arr = pg_fetch_assoc($result);

if (je_datum($datknjiz)) {
$dug = $arr['dug'];
$trazi = $arr['trazi'];
$saldo = $arr['saldo'];
}

// echo $sql;

// echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";

echo "</table><br>\n";
echo "<table align=\"center\" border=\"1\" width=\"50%\">\n";

echo "<tr><td align=\"center\" colspan=\"4\"><font color=\"red\"><b>Kontrolni zbir naloga</b></font></td></tr>\n";

echo "<tr><td align=\"right\"><b>Dugovni: </b></td>\n";
echo "<td align=\"right\"><font color=\"navy\"><b>" . number_format($dug, 2, ',', '.') . "</b></font></td>\n";
echo "<td align=\"right\"><b>Potražni: </b></td>\n";
echo "<td align=\"right\"><font color=\"navy\"><b>" . number_format($trazi, 2, ',', '.') . "</b></font></td></tr>\n";

echo "<tr><td align=\"center\" colspan=\"4\"><font color=\"red\"><b>Saldo naloga</b></font></td></tr>\n";
echo "<tr><td align=\"right\" colspan=\"2\"><b>Saldo: </b></td>\n";
echo "<td colspan=\"2\" align=\"right\"><font color=\"navy\"><b>" . number_format($saldo, 2, ',', '.');
echo "</b></font></td></tr>\n";


pg_close($conn);

?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class=tbn cellSpacing=0 cellPadding=0 border=0 id="table265" width="100%">
	<tbody>
		<tr>
			<td width="22">
				<img border="0" src="../../images/icg/tb1_leftr.gif" width="39" height="22">
			</td>
			<td class=tbnbot style="background-image: url('../../images/icg/tb1_m.gif')">
				<b><span lang="en-us">&nbsp;&nbsp;&nbsp;&nbsp;</span></b>
			</td>
      <td class=tbnr width="22">
				<img height=22 alt="" src="../../images/icg/tb1_r.gif" width=39>
			</td>
		</tr>
	</tbody>
</table>
<p>&nbsp;</td>
</tr>
</table>
<div id="predlog_modal"></div>
</div>
</body>
</html>
