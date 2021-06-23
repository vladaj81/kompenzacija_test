<?php

//AKO JE PRITISNUTO DUGME PRIPREMI KOMPENZACIJU NA FRONTENDU
if (isset($_POST['datum'])) {

    //UPISIVANJE POSLATIH PODATAKA IZ FORME ZA PREDLOG KOMPENZACIJE U VARIJABLE
    $datum_stanja = $_POST['datum'];
    $partner = $_POST['sifra_partnera'];
    $radnik = $_POST['radnik'];
    $broj_kompenzacije = $_POST['broj_kompenzacije'];

    $niz_duguje = $_POST['niz_duguje'];
    $niz_potrazuje = $_POST['niz_potrazuje'];

    //DOBIJANJE TRENUTNE GODINE I KONVERTOVANJE U INT FORMAT
    $sistemska_godina = intval(date("Y"));


    //KREIRANJE OBJEKTA ZA SLANJE,FLAGA I PROMENJIVE ZA GRESKE I PORUKU
    $ret = new stdClass();
    $flag = true;
    $greske = '';
    $poruka = '';


    //KREIRANJE KONEKCIJE KA AMSO BAZI
    $amso_konekcija = pg_connect("host=localhost dbname=amso user=zoranp");

    //AKO DODJE DO GRESKE PRI KONEKCIJI KA BAZI
    if (!$amso_konekcija) {

        $greske .= 'Greška u konekciji sa bazom podataka.';
        $flag = false;
    }

    //AKO JE KONEKCIJA OK,NASTAVI DALJE
    if ($flag) {

        //ZAPOCINJANJE TRANSAKCIJE
        $sql_transakcija 	= "BEGIN";
        $rezultat_transakcija 	= pg_query($amso_konekcija, $sql_transakcija);

        //UPIT ZA DOBIJANJE POSLEDNJEG SISTEMSKOG BROJA KOMPENZACIJE IZ TABELE KOMPENZACIJA ZAGLAVLJE
        $upit_sistemski_broj = "SELECT max(sistemski_broj) FROM kompenzacija_zaglavlje";

        //IZVRSAVANJE UPITA
        $rezultat_sis_broj = pg_query($amso_konekcija, $upit_sistemski_broj);

        //KREIRAJ PORUKU AKO JE DOSLO DO GRESKE PRI IZVRSAVANJU UPITA
        if (!$rezultat_sis_broj) {

            $greske .= 'Greška pri izvršavanju upita '.$upit_sistemski_broj;
            $flag = false;
        }

        //AKO UPIT VRATI REZULTATE
        else {

            $red_sis_broj = pg_fetch_array($rezultat_sis_broj); 

            //KREIRANJE SISTEMSKOG BROJA ZA UNOS,UVECAVANJEM POSLEDNJEG SISTEMSKOG BROJA ZA 1
            $sistemski_broj = $red_sis_broj['max'] + 1;

            //UPIT ZA UNOS U TABELU KOMPENZACIJA ZAGLAVLJE.UPIT TAKODJE VRACA ID POSLEDNJEG UNETOG REDA
            $upit_zaglavlje = "INSERT INTO kompenzacija_zaglavlje(datum_stanja, partner, broj_kompenzacije, sistemski_broj, sistemska_godina, datum_slanja_pdfa, datum_vracanja_pdfa) VALUES('$datum_stanja', '$partner', '$broj_kompenzacije', '$sistemski_broj', '$sistemska_godina', null, null) RETURNING kompenzacija_zaglavlje_id";
            
            //IZVRSAVANJE UPITA
            $rezultat = pg_query($amso_konekcija, $upit_zaglavlje);

            //KREIRAJ PORUKU AKO JE DOSLO DO GRESKE PRI IZVRSAVANJU UPITA
            if (!$rezultat) {

                $greske .= 'Greška pri izvršavanju upita ' .$upit_zaglavlje;
                $flag = false;
            }

            //AKO UPIT VRATI REZULTATE
            else {

                $red = pg_fetch_array($rezultat); 

                //DOBIJANJE ID-JA POSLEDNJEG UNETOG REDA IZ TABELE KOMPENZACIJA_ZAGLAVLJE
                $poslednji_id = $red['kompenzacija_zaglavlje_id'];
            

                //UPIT ZA KREIRANJE LOGA U TABELI KOMPENZACIJA_AKCIJE_LOG ZA NOVOUNETU KOMPENZACIJU
                $upit_akcije_log = "INSERT INTO kompenzacija_akcije_log(kompenzacija_id, akcija, radnik, datum_promene_statusa) VALUES('$poslednji_id', 'kreirano', '$radnik', 'now()')";

                //IZVRSAVANJE UPITA
                $rezultat_log = pg_query($amso_konekcija, $upit_akcije_log);

                //KREIRAJ PORUKU AKO JE DOSLO DO GRESKE PRI IZVRSAVANJU UPITA
                if (!$rezultat_log) {

                    $greske .= 'Greška pri izvršavanju upita ' .$upit_akcije_log;
                    $flag = false;
                }

                else {

                    //ZA SVAKU PREDLOZENU STAVKU SA DUGOVNE STRANE
                    foreach ($niz_duguje as $predlog_duguje) {

                        //UPIS VREDNOSTI IZ POLJA U PROMENLJIVE
                        $kompenzacija_zaglavlje_id = $poslednji_id;
                        $konto = $predlog_duguje[2];
                        $broj_dok = $predlog_duguje[3];
                        $duguje = $predlog_duguje[1];
                        $opis_dok = $predlog_duguje[5];
                        $potrazuje = 0;
                    
                        if ($predlog_duguje[4] != null) {

                            $kanal_prodaje = (int)$predlog_duguje[4];
                        }
                        else {
                            $kanal_prodaje = 'null';
                        }
                        
                        //UPIT ZA UNOS U TABELU KOMPENZACIJA STAVKE
                        $upit_stavke_duguje = "INSERT INTO kompenzacija_stavke(kompenzacija_zaglavlje_id, konto, brojdok, duguje, potrazuje, kanal_prodaje, opisdok) VALUES('$kompenzacija_zaglavlje_id', '$konto', '$broj_dok', '$duguje', '$potrazuje', $kanal_prodaje, '$opis_dok')";

                        //IZVRSAVANJE UPITA
                        $rezultat_duguje = pg_query($amso_konekcija, $upit_stavke_duguje);

                        //KREIRAJ PORUKU AKO JE DOSLO DO GRESKE PRI IZVRSAVANJU UPITA
                        if (!$rezultat_duguje) {

                            $greske .= 'Greška pri izvršavanju upita ' .$upit_stavke_duguje;
                            $flag = false;
                        }
                    }
                    
                    //ZA SVAKU PREDLOZENU STAVKU SA POTRAZNE STRANE
                    foreach ($niz_potrazuje as $predlog_potrazuje) {

                        //UPIS VREDNOSTI IZ POLJA U PROMENLJIVE
                        $kompenzacija_zaglavlje_id = $poslednji_id;
                        $konto = $predlog_potrazuje[2];
                        $broj_dok = $predlog_potrazuje[3];
                        $potrazuje = $predlog_potrazuje[1];
                        $opis_dok = $predlog_potrazuje[5];
                        $duguje = 0;

                        if ($predlog_potrazuje[4] != 0) {

                            $kanal_prodaje = (int)$predlog_potrazuje[4];
                        }
                        else {
                            $kanal_prodaje = 'null';
                        }

                        //UPIT ZA UNOS U TABELU KOMPENZACIJA STAVKE
                        $upit_stavke_potrazuje = "INSERT INTO kompenzacija_stavke(kompenzacija_zaglavlje_id, konto, brojdok, duguje, potrazuje, kanal_prodaje, opisdok) VALUES('$kompenzacija_zaglavlje_id', '$konto', '$broj_dok', '$duguje', '$potrazuje', $kanal_prodaje, '$opis_dok')";

                        //IZVRSAVANJE UPITA
                        $rezultat_potrazuje = pg_query($amso_konekcija, $upit_stavke_potrazuje);

                        //KREIRAJ PORUKU AKO JE DOSLO DO GRESKE PRI IZVRSAVANJU UPITA
                        if (!$rezultat_potrazuje) {

                            $greske .= 'Greška pri izvršavanju upita ' .$upit_stavke_potrazuje;
                            $flag = false;
                        }
                    }
                }
            }
        }
    }

    //AKO JE SVE OK,IZVRSI TRANSAKCIJU
    if ($flag) {

        $sql_transakcija = "COMMIT";
        $rezultat_transakcija = pg_query($amso_konekcija, $sql_transakcija);
        $poruka .= 'Predlog kompenzacije i stavke uspešno sačuvani u bazi.';

        $ret->poruka = $poruka;
    }
    //AKO DODJE DO GRESKE,PONISTI TRANSAKCIJU
    else {

        $sql_transakcija = "ROLLBACK";
        $rezultat_transakcija = pg_query($baza_amso, $sql_transakcija);

        $ret->greske = $greske;
    }

    $ret->flag = $flag;

    echo json_encode($ret);
}

