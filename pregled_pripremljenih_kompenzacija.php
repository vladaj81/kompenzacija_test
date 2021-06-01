<?php

session_start();

$root = $_SERVER ["DOCUMENT_ROOT"]; 
require_once "$root/common/no_cache.php";
require_once "$root/privilegije/privilegije.php";
require_once "$root/common/zabrane.php";
$sifra_u_nizu = array('001001056');
$sifra_provera= implode("','",$sifra_u_nizu);
zabrana_istekla_sesija($sifra_provera, $root);

if($_SESSION['radnik'])
{	
    $radnik=$_SESSION['radnik'];
}
else
{
    session_destroy();
    header("Location:/index.html");
    exit;
    
}
?>
<!DOCTYPE html>
<html>
<head>

    <title>Pregled pripremljenih kompenzacija</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!--UKLJUCIVANJE POTREBNIH CSS FAJLOVA-->
    <link rel="stylesheet" type="text/css" href="css/jquery-ui.css"/>
    <link rel="stylesheet" type="text/css" href="css/kompenzacije.css"/>
    <link rel="stylesheet" href="css/bootstrap.min.css">
   

    <!--UKLJUCIVANJE POTREBNIH SKRIPTI-->
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="js/jquery.alphanum.js"></script>
	<script type="text/javascript" src="js/jquery-ui.js"></script>	
	<script type="text/javascript" src="js/jquery.ui.datepicker-sr-SR.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>

</head>

<body>
    
    <!--GLAVNI DIV NA STRANICI POCETAK-->
    <div class="container-fluid podaci">
    
    </div>
    <!--GLAVNI DIV NA STRANICI KRAJ-->

    <!--MODALNI PROZOR SA DATEPICKEROM POCETAK-->
    <div class="modal fade" id="modal_datumi" role="dialog">
        
        <!--MODALNI DIJALOG POCETAK-->
        <div class="modal-dialog">
    
            <!--SADRZAJ MODALNOG PROZORA POCETAK-->
            <div class="modal-content">

                <!--ZAGLAVLJE MODALNOG PROZORA-->
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title naslov_modal">Odaberite datum</h4>
                </div>

                <!--TELO MODALNOG PROZORA-->
                <div class="modal-body">

                    <!--FORMA ZA BIRANJE DATUMA SLANJA PDF-A-->
                    <form id="forma_poslato" method="POST" hidden>
                        <input id="datum_poslato" name="datum_poslato" autocomplete="off" readonly="true"/>
                        <button type="submit" class="btn btn-xs btn-primary" value="potvrdi">Potvrdi</button>
                    </form>

                    <label id="label_poslato"></label>

                    <!--FORMA ZA BIRANJE DATUMA VRACANJA PDF-A-->
                    <form id="forma_vraceno" method="POST" hidden>
                        <input id="datum_vraceno" name="datum_vraceno" autocomplete="off" readonly="true"/>
                        <button type="submit" class="btn btn-xs btn-primary" value="potvrdi">Potvrdi</button>
                    </form>

                    <label id="label_vraceno"></label>

                </div>

                <!--FOOTER MODALNOG PROZORA-->
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Zatvori</button>
                </div>
                
            </div>
            <!--SADRZAJ MODALNOG PROZORA KRAJ-->
        
        </div>
        <!--MODALNI DIJALOG KRAJ-->

    </div>
    <!--MODALNI PROZOR SA DATEPICKEROM KRAJ-->



    <!--MODALNI PROZOR SA DATEPICKEROM ZA DATUM KNJIZENJA POCETAK-->
    <div class="modal fade" id="modal_knjizenje" role="dialog">
        
        <!--MODALNI DIJALOG POCETAK-->
        <div class="modal-dialog">
    
            <!--SADRZAJ MODALNOG PROZORA POCETAK-->
            <div class="modal-content">

                <!--ZAGLAVLJE MODALNOG PROZORA-->
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title naslov_modal">Odaberite datum naloga</h4>
                </div>

                <!--TELO MODALNOG PROZORA-->
                <div class="modal-body">

                    <!--FORMA ZA BIRANJE DATUMA SLANJA PDF-A-->
                    <form id="forma_knjizenje" method="POST" hidden>
                        <input id="datum_knjizenje" name="datum_knjizenje" autocomplete="off" readonly="true"/>

                        <input type="hidden" id="id_kompenzacije">
                        <input type="hidden" id="broj_kompenzacije">
                        <input type="hidden" id="pravno_lice">
                        <input type="hidden" id="pib">
                        <input type="hidden" id="iznos_kompenzacije">
                        <input type="hidden" id="datum_kompenzacije">

                        <button type="submit" class="btn btn-xs btn-primary" value="potvrdi">Potvrdi</button>
                    </form>

                    <label id="label_knjizenje"></label>

                </div>

                <!--FOOTER MODALNOG PROZORA-->
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Zatvori</button>
                </div>
                
            </div>
            <!--SADRZAJ MODALNOG PROZORA KRAJ-->
        
        </div>
        <!--MODALNI DIJALOG KRAJ-->

    </div>
    <!--MODALNI PROZOR SA DATEPICKEROM DATUM KNJIZENJA KRAJ-->

    <script>
    
    
    //SACEKAJ DA SE DOKUMENT UCITA
    $(document).ready(function() {

        //DEKLARISANJE PROMENJIVE ZA PRACENJE BROJA STRANICE
        var stranica;
        
        //INICIJALIZOVANJE OBJEKTA SA FILTERIMA
        var objekat_filteri = {'pib': null, 'status_knjizenja': null, 'status_pdfa': null};

        /******
        FUNKCIJA ZA UCITAVANJE PREDLOGA KOMPENZACIJA SA PAGINACIJOM

        -PRVI PARAMETAR JE BROJ STRANICE.AKO NEMA VREDNOST,PO DEFAULT-U SE UCITAVA PRVA STRANICA.
        -DRUGI PARAMETAR JE OBJEKAT SA FILTERIMA.
        ********/
        function ucitaj_filtrirane_podatke(stranica, filteri)  
        {  
            //SLANJE AJAX POZIVA U FAJL ZA GENERISANJE FILTRIRANIH PODATAKA SA PAGINACIJOM
            $.ajax({  

                url:"ajax/paginacija_novi_filteri.php",  
                method:"POST",
                dataType: 'json',  
                data:{stranica:stranica, filteri:filteri},  

                success:function(data){  
                    //console.log(data);

                    //AKO NEMA REZULTATA PRETRAGE,OBAVESTI KORISNIKA I REFRESH-UJ STRANICU
                    if (data.greska) {

                        alert(data.greska);
                        location.reload(); 
                    }
                    //AKO IMA REZULTATA ZA ZADATU PRETRAGU
                    else {

                        //UPIS PODATAKA U GLAVNI DIV STRANICE
                        $('.podaci').html(data.html); 
                    }

                    //OGRANICENJE DA INPUT ZA PRETRAGU PO PIBU MOZE BITI SAMO BROJ
                    $('#input_pretraga').numeric();
                }  
            });  
        }


        //POZIVANJE FUNKCIJE ZA PAGINACIJU,SA BROJEM STRANICE BEZ VREDNOSTI.(DA BI SE UCITALA PRVA STRANICA PO DEFAULTU).FILTERI SU TAKODJE PRAZNI
        ucitaj_filtrirane_podatke(stranica, objekat_filteri);  

        //FUNKCIJA NA KLIK DUGMETA PRETRAZI
        $(document).on('submit', '#forma_filteri', function(event) {

            //POSTAVLJANJE STRANICE PAGINACIJE NA 1
            stranica = '1';

            event.preventDefault();

            //AKO JE POLJE PRAZNO,OBAVESTI KORISNIKA
            if ($('#input_pretraga').val() == '') {

                alert('Niste uneli PIB za pretragu.');
            }
            //AKO JE UNETA VREDNOST RAZLICITA OD 9 KARAKTERA,OBAVESTI KORISNIKA I RESETUJ VREDNOST
            else if ($('#input_pretraga').val().length != 9) {

                alert('PIB mora imati 9 cifara. Ponovite unos.');
                $('#input_pretraga').val('');
            }
            //AKO JE SVE OK,SETUJ VREDNOST FILTERA NA VREDNOST PIBA IZ INPUT POLJA
            else {

                var filter_po_pibu =  $('#input_pretraga').val();
                var filter_po_statusu = $('#pretraga_kriterijumi').val();
                var filter_po_pdfu = $('#pretraga_pdf').val();

                objekat_filteri['pib'] = filter_po_pibu;
                objekat_filteri['status_knjizenja'] = filter_po_statusu;
                objekat_filteri['status_pdfa'] = filter_po_pdfu;

                //console.log(objekat_filteri);
                
                //POZIVANJE FUNKCIJE ZA UCITAVANJE PODATAKA,SETOVANJE STRANICE NA 1 I PROSLEDJIVANJE VRSTE I VREDNOSTI FILTERA
                ucitaj_filtrirane_podatke(1, objekat_filteri);
            }
        });

        //FUNKCIJA NA KLIK DUGMETA RESETUJ FILTERE
        $(document).on('click', '.resetuj_filtere', function() {

            location.reload();
        })

        //FUNKCIJA NA KLIK LINKA ZA PAGINACIJU
        $(document).on('click', '.pagination_link', function(){  

            //UZIMANJE BROJA STRANICE
            stranica = $(this).attr('id'); 

            //POZIVANJE FUNKCIJE ZA UCITAVANJE PODATAKA,SETOVANJE STRANICE NA BROJ KLIKNUTOG LINKA I PROSLEDJIVANJE VRSTE I VREDNOSTI FILTERA
            ucitaj_filtrirane_podatke(stranica, objekat_filteri);  
        });  

        
        //FUNKCIJA NA KLIK DUGMETA SA KLASOM STAVKE
        $(document).on('click', '.stavke', function() {

            //UZIMANJE VREDNOSTI ID-JA PREDLOGA KOMPENZACIJE,NA OSNOVU KLIKNUTOG DUGMETA
            var id_kompenzacije = $(this).attr('id');
            
            $.ajax({

                url: 'ajax/prikazi_stavke.php',
                method: 'POST',
                dataType: 'json',

                //PROSLEDJIVANJE ID-JA U BACKEND,DA BI SE DOBILE SVE STAVKE KOJE SADRZI ODREDJENI PREDLOG
                data: {id_kompenzacije:id_kompenzacije},

                success: function(data) {

                    //UPIS DOBIJENIH STAVKI U HTML
                    $('.podaci').html(data);
                }
           });
        });


        //FUNKCIJA NA KLIK DUGMETA SA KLASOM KRAJ.SLUZI ZA POVRATAK SA PRIKAZA STAVKI NA SVE PREDLOGE KOMPENZACIJE
        $(document).on('click', '.kraj', function() {

            //POZIVANJE FUNKCIJE ZA UCITAVANJE PODATAKA.PROSLEDJUJE SE TRENUTNI BROJ STRANICE,VRSTA I VREDNOST FILTERA
            ucitaj_filtrirane_podatke(stranica, objekat_filteri);  
        });


        //FUNKCIJA ZA KREIRANJE DIJALOG BOKSA,ZA POTVRDU AKCIJE KORISNIKA.PARAMETAR JE PORUKA KORISNIKU
        function potvrdi_akciju(poruka) {

            //UPIS STATUSA DA LI JE AKCIJA U DIJALOG BOXU POTVRDJENA ILI OTKAZANA
            var status_potvrde = confirm(poruka);

            //AKO JE AKCIJA POTVRDJENA,VRATI TRUE.U SUPROTNOM VRATI FALSE
            if (status_potvrde == true) {

                return true;
            }
            else {
                return false;
            }
        }
        /*
        //FUNKCIJA NA KLIK DUGMETA SA KLASOM ODOBRI - ZAKOMENTARISANO,DOK SE NE VIDI DA LI CE SE KORISTITI FUNKCIONALNOST
        $(document).on('click', '.odobri', function() {

            //POZIVANJE FUNKCIJE ZA OTVARANJE DIJALOG BOXA
            var potvrda_odobrenja = potvrdi_akciju("Da li sigurno želite da odobrite kompenzaciju?");

            //AKO JE DIJALOG BOX POTVRDJEN,NASTAVI SA ODOBRAVANJEM
            if (potvrda_odobrenja) {

                //UPISIVANJE VREDNOSTI ID-JA DUGMETA I ULOGOVANOG RADNIKA U PROMENJIVE
                var id_odobri = $(this).attr('id');
                //DODATI VREDNOST IZ SESIJE,AKO SE BUDE KORISTILA FUNKCIONALNOST
                //var radnik = '';

                $.ajax({

                    url: 'ajax/odobri.php',
                    method: 'POST',
                    dataType: 'json',

                    data: {id_odobri:id_odobri, radnik:radnik},

                    success: function(data) {
                        //console.log(data);
                        
                        //AKO JE VRACEN STATUS ODOBRENO,OSVEZI SADRZAJ STRANICE I OBAVESTI KORISNIKA
                        if (data.odobreno !== undefined) {

                            //POZIVANJE FUNKCIJE ZA UCITAVANJE PODATAKA. PROSLEDJUJE SE TRENUTNI BROJ STRANICE I OBJEKAT SA FILTERIMA
                            ucitaj_filtrirane_podatke(stranica, objekat_filteri);  
                            alert(data.odobreno); 
                        }

                        //AKO JE VRACENO OBAVESTENJE,PRIKAZI ALERT KORISNIKU
                        if (data.obavestenje !== undefined) {

                            alert(data.obavestenje);
                        }
                        
                    }
                });
            }
        });
        */

        //FUNKCIJA NA KLIK DUGMETA SA KLASOM STORNIRAJ
        $(document).on('click', '.storniraj', function() {

            //POZIVANJE FUNKCIJE ZA OTVARANJE DIJALOG BOXA
            var potvrda_storniranja = potvrdi_akciju("Da li sigurno želite da stornirate kompenzaciju?");

            //AKO JE DIJALOG BOX POTVRDJEN,NASTAVI SA STORNIRANJEM
            if (potvrda_storniranja) {
                
                //UPISIVANJE VREDNOSTI ID-JA DUGMETA I ULOGOVANOG RADNIKA U PROMENJIVE
                var id_storniraj = $(this).attr('id');
                var radnik = <?php echo $_SESSION['radnik']; ?>;

                $.ajax({

                    url: 'ajax/storniraj.php',
                    method: 'POST',
                    dataType: 'json',

                    data: {id_storniraj:id_storniraj, radnik:radnik},

                    success: function(data) {
                        //console.log(data);
                        
                        //AKO JE VRACEN STATUS STORNIRANO,OSVEZI SADRZAJ STRANICE I OBAVESTI KORISNIKA
                        if (data.stornirano != undefined) {

                            //POZIVANJE FUNKCIJE ZA UCITAVANJE PODATAKA.PROSLEDJUJE SE TRENUTNI BROJ STRANICE I OBJEKAT SA FILTERIMA
                            ucitaj_filtrirane_podatke(stranica, objekat_filteri);  
                            alert(data.stornirano); 
                        }

                        //AKO JE VRACENO OBAVESTENJE,PRIKAZI ALERT KORISNIKU
                        if (data.obavestenje !== undefined) {

                            alert(data.obavestenje);
                        }
                        
                    }
                });
            }
        });


        //DATEPICKER FUNKCIJA ZA DATUM SLANJA
        $("#datum_poslato").datepicker({

            showButtonPanel: false,
            maxDate: '0',
            dateFormat: 'yy-mm-dd',
        });

        //KREIRANJE PROMENJIVE ZA UPIS ID-JA POSLATOG PDF-A
        var id_poslat_pdf;

        //FUNKCIJA NA KLIK DUGMETA POSLAT PDF
        $(document).on('click', '.poslat_pdf', function() {

            //UPISIVANJE VREDNOSTI ODGOVARAJUCEG ID-JA U PROMENJIVU
            id_poslat_pdf = $(this).attr('id');

            //RESETOVANJE I SAKRIVANJE ZELJENIH HTML ELEMENATA
            $('#label_vraceno').html('');
            $('#forma_vraceno').hide();
            $('#label_poslato').html('');

            //PRIKAZ FORME SA DATEPICKER-OM I MODALNOG PROZORA
            $('#forma_poslato').show();
            $('#modal_datumi').modal('show');
        });


        //FUNKCIJA NA POTVRDU FORME ZA BIRANJE DATUMA
        $('#forma_poslato').on('submit', function(event) {  

            event.preventDefault();  

            //AKO DATUM NIJE ODABRAN,OBAVESTI KORISNIKA
            if ($('#datum_poslato').val() == '') {  
            
                $('#label_poslato').html('Niste odabrali datum');
            }  
            //U SUPROTNOM,PRIPREMI PODATKE ZA SLANJE I POSALJI AJAX POZIV
            else  
            {  
                //UZIMANJE VREDNOSTI DATUMA IZ DATEPICKER POLJA
                var datum_slanja = $('#datum_poslato').val();

                $.ajax({  

                    url: 'ajax/datum_slanja_pdfa.php',  
                    method: 'POST', 
                    dataType: 'json', 
                    data: {datum_slanja:datum_slanja, id_poslat_pdf:id_poslat_pdf},

                    success: function(data) {
                       
                        //RESETOVANJE POLJA SA DATUMOM 
                        $('#datum_poslato').val('');
                        
                        //AKO POSTOJI BILO KAKVA GRESKA,OBAVESTI KORISNIKA
                        if (data.status) {

                            $('#label_poslato').html(data.status);
                        }

                        //U SUPROTNOM,OBAVESTI KORISNIKA DA JE DATUM UNET U BAZU I OSVEZI PODATKE NA STRANICI
                        else {

                            $('#forma_poslato').hide();
                            $('#modal_datumi').modal('hide');

                            //POZIVANJE FUNKCIJE ZA UCITAVANJE PODATAKA.PROSLEDJUJE SE TRENUTNI BROJ STRANICE I OBJEKAT SA FILTERIMA
                            ucitaj_filtrirane_podatke(stranica, objekat_filteri);  
                            alert(data.uneseno); 
                        }
                    }
                });
            }
        });


        //DATEPICKER FUNKCIJA ZA DATUM VRACANJA
        $("#datum_vraceno").datepicker({

            showButtonPanel: false,
            maxDate: '0',
            dateFormat: 'yy-mm-dd',
        });

        //KREIRANJE PROMENJIVE ZA UPIS ID-JA VRACENOG PDF-A
        var id_vracen_pdf;

        //FUNKCIJA NA KLIK DUGMETA VRACEN PDF
        $(document).on('click', '.vracen_pdf', function() {

            //UPISIVANJE VREDNOSTI ODGOVARAJUCEG ID-JA U PROMENJIVE
            id_vracen_pdf = $(this).attr('id');

            //RESETOVANJE I SAKRIVANJE ZELJENIH HTML ELEMENATA
            $('#label_poslato').html('');
            $('#forma_poslato').hide();
            $('#label_vraceno').html('');

            //PRIKAZ FORME SA DATEPICKER-OM I MODALNOG PROZORA
            $('#forma_vraceno').show();
            $('#modal_datumi').modal('show');
        });


        //FUNKCIJA NA POTVRDU FORME ZA BIRANJE DATUMA
        $('#forma_vraceno').on('submit', function(event) {  

            event.preventDefault();  

            //AKO DATUM NIJE ODABRAN,OBAVESTI KORISNIKA.
            if ($('#datum_vraceno').val() == '') 
            {  
                $('#label_vraceno').html('Niste odabrali datum');
            }  
            //U SUPROTNOM,PRIPREMI PODATKE ZA SLANJE I POSALJI AJAX POZIV
            else  
            {  
                //UZIMANJE VREDNOSTI DATUMA IZ POLJA
                var datum_vracanja = $('#datum_vraceno').val();

                $.ajax({  

                    url: 'ajax/datum_vracanja_pdfa.php',  
                    method: 'POST', 
                    dataType: 'json', 
                    data: {datum_vracanja:datum_vracanja, id_vracen_pdf:id_vracen_pdf},

                    success: function(data) {
                        //console.log(data);

                        //RESETOVANJE POLJA SA DATUMOM
                        $('#datum_vraceno').val('');

                        //AKO POSTOJI BILO KAKVA GRESKA,OBAVESTI KORISNIKA
                        if (data.status) {

                            $('#label_vraceno').html(data.status);
                        }
                        //U SUPROTNOM,OBAVESTI KORISNIKA DA JE DATUM UNET U BAZU I OSVEZI PODATKE NA STRANICI
                        else {

                            $('#forma_vraceno').hide();
                            $('#modal_datumi').modal('hide');
                            
                            //POZIVANJE FUNKCIJE ZA UCITAVANJE PODATAKA. PROSLEDJUJE SE TRENUTNI BROJ STRANICE I OBJEKAT SA FILTERIMA
                            ucitaj_filtrirane_podatke(stranica, objekat_filteri);  
                            alert(data.uneseno); 
                        }
                    }
                });
            }
        });



        //DATEPICKER FUNKCIJA ZA DATUM KNJIZENJA
        $("#datum_knjizenje").datepicker({

            showButtonPanel: false,
            maxDate: '0',
            dateFormat: 'yy-mm-dd',
            onSelect: function() {

                $('#label_knjizenje').html('');             
            }
        });

        //KREIRANJE PROMENJIVE ZA UPIS ID-JA KOMPENZACIJE KOJA SE KNJIZI
        var id_proknjizi;

        //FUNKCIJA NA KLIK DUGMETA PROKNJIZI
        $(document).on('click', '.proknjizi', function() {

            //RESETOVANJE POLJA FORME ZA KNJIZENJE
            $('#datum_knjizenje').val('');
            
            //UPISIVANJE VREDNOSTI ODGOVARAJUCEG ID-JA U PROMENJIVU
            id_proknjizi = $(this).attr('id');
            var funkcija = 'proveri_status_kompenzacije';
            
            //SLANJE AJAX POZIVA ZA PROVERU DA LI SU ISPUNJENI USLOVI ZA KNJIZENJE KOMPENZACIJE
            $.ajax({  

                url: 'ajax/knjizenje_kompenzacija.php',  
                method: 'POST', 
                dataType: 'json', 
                data: {id_proknjizi:id_proknjizi, funkcija:funkcija},
                
                success: function(data) {

                    //console.log(data);

                    //OTVORI MODALNI PROZOR ZA IZBOR DATUMA KNJIZENJA
                    if (data.flag) {

                        $('#id_kompenzacije').val(data.id_kompenzacije);
                        $('#broj_kompenzacije').val(data.broj_kompenzacije);
                        $('#pravno_lice').val(data.pravno_lice);
                        $('#pib').val(data.pib);
                        $('#iznos_kompenzacije').val(data.iznos_kompenzacije);
                        $('#datum_kompenzacije').val(data.datum_kompenzacije);

                        $('#forma_knjizenje').show();
                        $('#modal_knjizenje').modal('show');
                    }
                    //AKO POSTOJI BILO KAKVA GRESKA,OBAVESTI KORISNIKA
                    else {
                        alert(data.greske);
                    }
                }
            });
        });


        //FUNKCIJA NA POTVRDU FORME ZA KNJIZENJE
        $('#forma_knjizenje').on('submit', function(event) {  

            event.preventDefault();  

            //AKO DATUM NIJE ODABRAN,OBAVESTI KORISNIKA
            if ($('#datum_knjizenje').val() == '') {  

                $('#label_knjizenje').html('Niste odabrali datum');
            }  
            //U SUPROTNOM,PRIPREMI PODATKE ZA SLANJE I POSALJI AJAX POZIV
            else  
            {  
                //BRISANJE TEKSTA IZ LABELE ZA GRESKE
                $('#label_knjizenje').html('');

                //UZIMANJE VREDNOSTI DATUMA IZ DATEPICKER POLJA
                var datum_knjizenja = $('#datum_knjizenje').val();

                //UZIMANJE ID-JA KOMPENZACIJE IZ FORME I RADNIKA IZ SESIJE
                var id_knjizenje = $('#id_kompenzacije').val();
                var radnik = <?php echo $_SESSION['radnik']; ?>;

                //KREIRANJE PODATAKA ZA UPIS U CONFIRM BOX
                var broj_kompenzacije = $('#broj_kompenzacije').val();
                var pravno_lice = $('#pravno_lice').val();
                var pib = $('#pib').val();
                var iznos_kompenzacije = $('#iznos_kompenzacije').val();
                var datum_kompenzacije = $('#datum_kompenzacije').val();


                //OTVARANJE CONFIRM BOXA
                var status = confirm("Da li sigurno želite da proknjižite kompenzaciju broj: " + broj_kompenzacije + '\n\n'
                
                + 'za partnera: ' + pravno_lice + '\n\n'
                
                + 'pib: ' + pib + '\n\n'
                
                + 'datum kompenzacije: ' + datum_kompenzacije + '\n\n'
                
                + 'iznos_kompenzacije: ' + iznos_kompenzacije);


                //AKO JE DIJALOG BOX POTVRDJEN,NASTAVI SA KNJIZENJEM
                if (status === true) {

                    var funkcija = 'proknjizi_kompenzaciju';

                    $.ajax({  

                        url: 'ajax/knjizenje_kompenzacija.php',  
                        method: 'POST', 
                        dataType: 'json', 
                        data: {datum_knjizenja:datum_knjizenja, id_knjizenje:id_knjizenje, radnik:radnik, funkcija:funkcija},

                        success: function(data) {
                        
                            //console.log(data);

                            //RESETOVANJE POLJA SA DATUMOM 
                            $("#datum_knjizenje").datepicker('setDate', '');
                            $('#forma_knjizenje').hide();
                            $('#modal_knjizenje').modal('hide');

                            //AKO JE SVE OK,PRIKAZI PORUKU
                            if (data.flag) {

                                alert(data.poruka);
                                
                                //POZIVANJE FUNKCIJE ZA UCITAVANJE PODATAKA.PROSLEDJUJE SE TRENUTNI BROJ STRANICE I OBJEKAT SA FILTERIMA
                                ucitaj_filtrirane_podatke(stranica, objekat_filteri);  
                            }
                            //U SUPROTNOM PRIKAZI GRESKU
                            else {

                                alert(data.greske);
                            }
                        }
                    });
                }
                else {
                    alert('Uspešno ste otkazali knjiženje kompenzacije.');
                }
            }
        });
    })
    </script>

</body>
</html>