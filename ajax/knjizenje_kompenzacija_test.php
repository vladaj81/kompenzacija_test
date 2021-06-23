<?php

//AKO JE KLIKNUTO NA DUGME PROKNJIZI I PROSLEDJEN ID KOMPENZACIJE,POZOVI FUNKCIJU ZA PROVERU STATUSA
if (isset($_POST['funkcija']) && $_POST['funkcija'] == 'proveri_status_kompenzacije') {

    proveri_status_kompenzacije($_POST['id_proknjizi']);
}

//FUNKCIJA ZA PROVERU STATUSA KOMPENZACIJE
function proveri_status_kompenzacije($id_kompenzacije) {

    //KREIRANJE OBJEKTA ZA SLANJE,FLAGA I PROMENJIVE ZA GRESKE
    $ret = new stdClass();
    $flag = true;
    $greske = '';

    //UPIS ID-JA KOMPENZACIJE U PROMENJIVU
    $kompenzacija_id = $_POST['id_proknjizi'];


    //UKLJUCIVANJE KONEKCIJE KA AMSO BAZI
    $amso_konekcija = pg_connect("host=localhost dbname=amso user=zoranp");

    //AKO DODJE DO GRESKE PRI KONEKCIJI KA BAZI
    if (!$amso_konekcija) {
        $greske .= 'Greška u konekciji sa bazom podataka.';
        $flag = false;
    }

    //AKO JE KONEKCIJA OK,NASTAVI DALJE
    if ($flag) {

        //UPIT ZA PROVERU POSLEDNJEG STATUSA KOMPENZACIJE SA ODREDJENIM ID-JEM
        $upit_provera = "SELECT akcija FROM kompenzacija_akcije_log WHERE kompenzacija_id = '$kompenzacija_id' 
                        ORDER BY datum_promene_statusa DESC LIMIT 1";
    
        //IZVRSAVANJE UPITA
        $rezultat_provera = pg_query($amso_konekcija, $upit_provera);
    
        //AKO DODJE DO GRESKE PRI IZVRSAVANJU UPITA
        if (!$rezultat_provera) {
    
            $greske .= 'Greška pri izvršavanju upita. Pokušajte ponovo.';
            $flag = false;
        }
        else {
    
            //UPISIVANJE DOBIJENOG REDA IZ BAZE U NIZ
            $red_provera = pg_fetch_array($rezultat_provera);
        
            //AKO JE DOKUMENT VEC PROKNJIZEN
            if ($red_provera['akcija'] == 'proknjizeno') {
        
                $greske .= 'Dokument je već proknjižen.';
                $flag = false;
            }
        
            //AKO JE DOKUMENT VEC STORNIRAN
            elseif ($red_provera['akcija'] == 'stornirano') {
        
                $greske .=  'Dokument je storniran. Ne možete izvršiti knjiženje.';
                $flag = false;
            }
            //AKO JE SVE OK
            else {
                
                //UPIT ZA DOBIJANJE PODATAKA O KOMPENZACIJI KOJI CE SE PRIKAZIVATI U ALERT CONFIRM BOXU
                $upit_podaci = "SELECT kz.kompenzacija_zaglavlje_id, kz.broj_kompenzacije, kz.partner, p.naziv,
                
                                SUM(ks.duguje) AS suma_duguje, kl.datum_promene_statusa 
                                
                                FROM kompenzacija_zaglavlje kz

                                INNER JOIN kompenzacija_stavke ks ON kz.kompenzacija_zaglavlje_id = ks.kompenzacija_zaglavlje_id

                                INNER JOIN kompenzacija_akcije_log kl ON kl.kompenzacija_id = kz.kompenzacija_zaglavlje_id 

                                INNER JOIN partneri p ON p.sifra = kz.partner 

                                WHERE kz.kompenzacija_zaglavlje_id = '$kompenzacija_id' AND kl.akcija = 'kreirano'

                                GROUP BY kz.kompenzacija_zaglavlje_id, kz.broj_kompenzacije, kz.partner, p.naziv, kl.datum_promene_statusa";

                //IZVRSAVANJE UPITA
                $rezultat_podaci = pg_query($amso_konekcija, $upit_podaci);
    
                //AKO DODJE DO GRESKE PRI IZVRSAVANJU UPITA
                if (!$rezultat_podaci) {
            
                    $greske .= 'Greška pri izvršavanju upita. Pokušajte ponovo.';
                    $flag = false;
                }
                else {

                    //UPISIVANJE DOBIJENOG REDA IZ BAZE U NIZ
                    $red_podaci = pg_fetch_array($rezultat_podaci);

                    //UPISIVANJE PODATAKA O KOMPENZACIJI U RET OBJEKAT
                    $ret->id_kompenzacije = $red_podaci['kompenzacija_zaglavlje_id'];
                    $ret->broj_kompenzacije = $red_podaci['broj_kompenzacije'];
                    $ret->pravno_lice = $red_podaci['naziv'];
                    $ret->pib = $red_podaci['partner'];
                    $ret->iznos_kompenzacije = $red_podaci['suma_duguje'];
                    $ret->datum_kompenzacije = substr($red_podaci['datum_promene_statusa'], 0, 10);
                }
            } 
        }
    }
    
    $ret->flag = $flag;

    //AKO POSTOJI BILO KOJA GRESKA
    if (!$flag) {
        $ret->greske = $greske;
    }

    echo json_encode($ret);
}
/*
//HARDCODE-OVANO ZBOG TESTIRANJA
$_POST['funkcija'] = 'proknjizi_kompenzaciju';
$_POST['id_knjizenje'] = 189;
$_POST['datum_knjizenja'] = '2021-05-01';
$radnik = 151;
*/

//AKO JE KLIKNUTO NA DUGME KNJIZENJE I PROSLEDJEN ID KOMPENZACIJE,POZOVI FUNKCIJU ZA KNJIZENJE U GLAVNOJ KNJIZI
if (isset($_POST['funkcija']) && $_POST['funkcija'] == 'proknjizi_kompenzaciju') {

    proknjizi_kompenzaciju($_POST['id_knjizenje'], $_POST['datum_knjizenja']);
}


//FUNKCIJA ZA KNJIZENJE KOMPENZACIJA NA OSNOVU ID-JA KOMPENZACIJE I DATUMA NALOGA
function proknjizi_kompenzaciju($id_kompenzacije, $datum_naloga) {

    require_once('../../../common/zakljucano.php');

    //SETOVANJE DATUMA DOSPECA NA DATUM NALOGA - ZBOG SLUCAJA KADA JE DATUM POTPISA KOMPENZACIJE U ZAKLJUCANOM PERIODU
    $datum_dospeva = $datum_naloga;

    //PROVERA I SETOVANJE DATUMA NALOGA U ODNOSU NA ZAKLJUCAN PERIOD
    $datum_naloga = proveriZDatum('KVART', $datum_naloga);

    //KREIRANJE OBJEKTA ZA SLANJE,FLAGA I PROMENJIVE ZA GRESKE
    $ret = new stdClass();
    $flag = true;
    $greske = '';
    
  
    //UKLJUCIVANJE KONEKCIJE KA AMSO BAZI
    $amso_konekcija = pg_connect("host=localhost dbname=amso user=zoranp");

    //AKO DODJE DO GRESKE PRI KONEKCIJI KA BAZI
    if (!$amso_konekcija) {

        $greske .= 'Greška u konekciji sa bazom podataka.';
        $flag = false;
    }

    //AKO JE KONEKCIJA OK,NASTAVI DALJE
    if ($flag) {

        //UPIT ZA DOBIJANJE BROJA KOMPENZACIJE I PARTNERA
        $upit_podaci = "SELECT kz.broj_kompenzacije, kz.partner, ks.konto, ks.brojdok, ks.opisdok, ks.duguje, ks.potrazuje, ks.kanal_prodaje, p.naziv AS naziv_partnera 

                        FROM kompenzacija_zaglavlje kz

                        INNER JOIN kompenzacija_stavke ks on kz.kompenzacija_zaglavlje_id = ks.kompenzacija_zaglavlje_id

                        INNER JOIN partneri p ON p.sifra = kz.partner 

                        WHERE kz.kompenzacija_zaglavlje_id = '$id_kompenzacije'

                        ORDER BY ks.kompenzacija_stavke_id";

        //IZVRSAVANJE UPITA
        $rezultat_podaci = pg_query($amso_konekcija, $upit_podaci);

        //AKO DODJE DO GRESKE PRI IZVRSAVANJU UPITA
        if (!$rezultat_podaci) {
    
            $greske .= 'Greška pri izvršavanju upita. Pokušajte ponovo.';
            $flag = false;
        }
        else {

            //UPISIVANJE PODATAKA IZ BAZE U NIZ
            $niz_podaci = pg_fetch_all($rezultat_podaci);

            //IZDVAJANJE GODINE IZ DATUMA NALOGA
            $godina = substr($datum_naloga, 0, 4);


            //ZAPOCINJANJE TRANSAKCIJE
            $sql_transakcija 	= "BEGIN";
            $rezultat_transakcija 	= pg_query($amso_konekcija, $sql_transakcija);


            //POCETAK UPITA ZA KNJIZENJE KOMPENZACJE U GLAVNOJ KNJIZI
            $upit_knjizenje = "INSERT INTO g$godina (datknjiz, vrstadok, brdok, ff, partner, pib, ppsi, opisdok, brojdok, datdok,
             
            dospeva, duguje, potrazuje, opetnalog, konto, mnt, radnik, knjizdana, vremknjiz) VALUES ";


            //PROLAZAK KROZ NIZ SA SVIM STAVKAMA KOMPENZACIJE
            foreach($niz_podaci as $stavka_kompenzacije) {

                //SETOVANJE VREDNOSTI ZA UNOS U BAZU
                $brdok = $stavka_kompenzacije['broj_kompenzacije'];
                $brojdok = $stavka_kompenzacije['brojdok'];
                $pib_partnera = $stavka_kompenzacije['partner'];
                $konto = $stavka_kompenzacije['konto'];

                //KREIRANJE OPISA DOKUMENTA I DODAVANJE NAZIVA PARTNERA NA POCETKU
                $opis_dokumenta = $stavka_kompenzacije['naziv_partnera'] .' '. $stavka_kompenzacije['opisdok'];

                //AKO ZA STAVKU NIJE UNET KANAL PRODAJE, PODESI VREDNOST NA NULL
                if ($stavka_kompenzacije['kanal_prodaje'] == '' || $stavka_kompenzacije['kanal_prodaje'] == null) {

                    $kanal_prodaje = 'NULL';
                }
                //U SUPROTNOM,UPISI VREDNOST IZ BAZE
                else {

                    $kanal_prodaje = $stavka_kompenzacije['kanal_prodaje'];
                }

                //AKO JE STAVKA KOMPENZACIJE NA DUGOVNOJ STRANI
                if ($stavka_kompenzacije['duguje'] > 0) {

                    $duguje = $stavka_kompenzacije['duguje'];
                    $potrazuje = 0;
                }
                //AKO JE STAVKA KOMPENZACIJE NA POTRAZNOJ STRANI
                else {

                    $duguje = 0;
                    $potrazuje = $stavka_kompenzacije['potrazuje'];
                }

                
                //NASTAVAK UPITA,TJ. DODAVANJE VREDNOSTI ZA SVAKU STAVKU KOMPENZACIJE
                $upit_knjizenje .= "('$datum_naloga', 'KM', '$brdok', 'F', '$pib_partnera', $kanal_prodaje, 'PP', '$opis_dokumenta','$brojdok', 
                '$datum_naloga', '$datum_dospeva', '$duguje', '$potrazuje', '$brdok', '$konto', '111000', $radnik, current_date, current_time),";

            }

            //UKLANJANJE ZAREZA SA KRAJA UPITA
            $upit_knjizenje = rtrim($upit_knjizenje, ',');

            //IZVRSAVANJE UPITA ZA KNJIZENJE
            $rezultat_knjizenje = pg_query($amso_konekcija, $upit_knjizenje);

            //AKO DODJE DO GRESKE PRI IZVRSAVANJU UPITA
            if (!$rezultat_knjizenje) {
        
                $flag = false;

                //SKLONITI NAKON TESTIRANJA
                $greske .= 'Greška u upitu za knjiženje u glavnoj knjizi: ' .$upit_knjizenje;
            }


            //UPIT ZA UNOS STATUSA PROKNJIZENO U TABELU KOMPENZACIJA_AKCIJE_LOG ZA ZELJENU KOMPENZACIJU 
            $upit_status = "INSERT INTO kompenzacija_akcije_log(kompenzacija_id, akcija, radnik, datum_promene_statusa) VALUES ('$id_kompenzacije', 'proknjizeno', '$radnik', 'now()')";

            //IZVRSAVANJE UPITA
            $rezultat_status = pg_query($amso_konekcija, $upit_status);
    
            //AKO DODJE DO GRESKE PRI IZVRSAVANJU UPITA
            if (!$rezultat_status) {
                
                $flag = false;

                //SKLONITI NAKON TESTIRANJA
                $greske .= 'Greška u upitu za ažuriranje statusa kompenzacije: ' .$upit_status;
            }



            //AKO JE SVE OK,IZVRSI TRANSAKCIJU
            if ($flag) {

                $sql_transakcija = "COMMIT";
                $rezultat_transakcija = pg_query($amso_konekcija, $sql_transakcija);
                $poruka .= "Uspešno ste proknjižili kompenzaciju broj: " . $brdok;

              
            }
            //AKO DODJE DO GRESKE,PONISTI TRANSAKCIJU
            else {

                $sql_transakcija = "ROLLBACK";
                $rezultat_transakcija = pg_query($baza_amso, $sql_transakcija);
                $greske .= "Knjiženje kompenzacije broj: "  .$brdok. " nije uspelo.";
            }
        }
    }

    //AKO POSTOJI GRESKA,UPISI GRESKU
    if (!$flag) {

        $ret->greske = $greske;
    }
    //AKO JE SVE OK,UPISI PORUKU
    else {

        $ret->poruka = $poruka;
    }

    $ret->flag = $flag;

    echo json_encode($ret);
}



