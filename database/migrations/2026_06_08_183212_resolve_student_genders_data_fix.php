<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $unspecified = DB::table('students')
            ->whereNull('gender')
            ->orWhere('gender', '')
            ->get();

        if ($unspecified->isEmpty()) {
            return;
        }

        $maleKeywords = [
            'MUHAMMAD', 'MOHAMMAD', 'MOH', 'AHMAD', 'ACHMAD', 'BAGUS', 'FADIL', 'SATRIA', 
            'BAYU', 'RAFI', 'FAHRI', 'ALIF', 'IKHSAN', 'ADITYA', 'YUSUF', 'IBRAHIM', 
            'FAJAR', 'REZA', 'HADI', 'EKO', 'AGUS', 'SAMSUL', 'BAHRI', 'PUTRA', 
            'FADHLURRAHMAN', 'RIZKY', 'ILHAM', 'ARIF', 'HIDAYAT', 'AKBAR',
            'WIRYA', 'BUDI', 'NUGROHO', 'SULTON', 'WAHAB', 'HASYIM', 'MUSTOFA',
            'HAMDAN', 'ALI', 'MUCHTAR', 'KARIM', 'SUBANDI', 'BROMO', 'ALAM', 'ALKHOLIDH',
            'PRAYOGO', 'SAPUTRA', 'LUDFI', 'KHOIRIL', 'EKSA', 'CHALEO', 'ARGAPRADITHA',
            'KHAFIS', 'ABY', 'ANANDIKA', 'DEVIN', 'FANSYURI', 'RAKHMAN', 'ABDILLAH', 'SYAHRIAL',
            'MAULANA', 'AZIZ', 'HABIB', 'FAUZAN', 'GALIH', 'DIMAS', 'FATHUR', 'WICAKSONO',
            'BIMA', 'PRABOWO', 'ZIKRI', 'LATHIF', 'ARIEF', 'ZAMZAMI', 'RAYA', 
            'ALFAREGA', 'TAHTA', 'ADILUHUNG', 'YUSRON', 'ABIDIN', 'FADLAN', 'FIRMANSYAH', 
            'ASYRAF', 'HABIBUSSYUJA', 'ASRO', 'MUKHSIN', 'ARYA', 'ALFAHRI', 
            'KHOTIBUL', 'UMAM', 'ALVIN', 'FEBRIYANTO', 'GADING', 'RENDI', 'PURNOMO', 
            'VICKY', 'GALIH', 'WISNU', 'WITJAKSONO', 'GHANI', 'GHUFRON', 'HAFIZ', 'HAIKAL',
            'HISYAM', 'IBNU', 'IHSAN', 'ILHAM', 'IRKHAM', 'KAMALUDIN', 'KEVIN', 'KHIDR',
            'KHOIRUL', 'KHUSNUL', 'KRISNA', 'MUSTAKIM', 'NAUFAL', 'NIZAR', 'REIHAN',
            'RENDY', 'REVAN', 'REYHAN', 'RIDHO', 'RIFKI', 'RIKZAN', 'RISVINO', 'RIZQI',
            'ROHMAN', 'SATRIO', 'SIDIK', 'SISWANTO', 'TAUFIQURROHMAN', 'THOHA', 'WAFIY',
            'YOGA', 'ZAIN', 'ZAINAL', 'ZIKRA', 'DAVID', 'DIAS', 'DZAKA', 'DZAKY',
            'EKY', 'FUAD', 'FURQON', 'PANDJI', 'RAFA', 'RAFAEL', 'RAFI', 'RAGEL', 'RANGGA',
            'REIHAN', 'RENDY', 'REVAN', 'REYHAN', 'RICKY', 'RIDHO', 'RIFKI', 'RIKZAN',
            'RISVINO', 'RIZQI', 'ROHMAN', 'SATRIO', 'SIDIK', 'SISWANTO', 'TAUFIQURROHMAN',
            'THOHA', 'WAFIY', 'YOGA', 'ZAIN', 'ZAINAL', 'ZIKRA', 'AMDAD', 'ANWAR',
            'ARROZAQ', 'ASLAMUD', 'AURIGA', 'AUFA', 'AZZAM', 'BAEHAQI', 'BASKORO', 'DERMAWAN',
            'ERLANGGA', 'ERVIN', 'FATA', 'FATONI', 'FAWWAZ', 'FAYYAZ', 'FIRDAUS', 'GUSWARA',
            'HADZIQUL', 'HILMY', 'IANDRA', 'IDDAM', 'IRSYAD', 'IZZUL', 'JAILANI', 'JIBRAN',
            'KAFI', 'KAMAL', 'KANZU', 'KEANU', 'KHOLILUR', 'KURNIAWAN', 'LAZUARDI', 'LUTHER',
            'MAHRI', 'MUBARAZ', 'MUHYIDDIN', 'NAJRIL', 'NATHAN', 'OKTAVIAN', 'PAHLEVI', 'PRAMUDYA',
            'PRATAMA', 'ROCHMAN', 'ROJAUN', 'ROSIFUL', 'ROZIQIN', 'SADAM', 'SAIFUL', 'SAMSUL',
            'SATRIYOSO', 'SENO', 'SHODIQIN', 'SUGIA', 'SUGIARTO', 'SYAHRIAL',
            'SYAIFUL', 'SYARIF', 'SULTHON', 'TARUNA', 'THUBA', 'TRISNA', 'VIANTO', 'WAFA',
            'WIBISONO', 'WICAKSONO', 'WILDAN', 'WITJAKSONO', 'YUSRON', 'ZAHDAN', 'ZAIMUL'
        ];

        $femaleKeywords = [
            'SITI', 'DEWI', 'PUTRI', 'SALSABILA', 'ANISA', 'ANISAH', 'KHOIRUNNISA', 
            'NADA', 'ZAHRA', 'BELLA', 'SARI', 'ZAKIA', 'ALIFIA', 'MARSYALIMA', 'ZAHWA', 
            'RARAS', 'WENINGTYASTUTI', 'HAJAR', 'KARTIKA', 'MUZAENAH', 'ATIK', 'SORAYA', 
            'ANJANI', 'JANNAH', 'AAISYAH', 'AADILLAH', 'MARSYA', 'AINUN',
            'NI\'MAH', 'NI\'MATUL', 'LATIFAH', 'FITRI', 'SABRINA', 'NILA', 'ZAHIRA', 'MUTIARA',
            'TSALTSABILA', 'FATIHAH', 'AIDA', 'NADIA', 'AMALIA', 'MUTI', 'ALMEERA', 'ALYA',
            'AMANDA', 'ANGGITA', 'ANINDYA', 'ARDIANTI', 'ARFIANI', 'ARUM', 'AVIVALEN',
            'AYUDIA', 'AYU', 'AZALEA', 'AZHIMA', 'AZZAHRO', 'BILQIS', 'CAHYANI', 'CINTA',
            'CITRA', 'CITRADWIPANI', 'DAHLIA', 'DAMAYANTI', 'DESI', 'DESY', 'DEVINA',
            'DHANIA', 'DIAN', 'DINDA', 'DIVA', 'ELFRIDA', 'ELISYA', 'ELYSA',
            'ENZELINA', 'ERIKA', 'ERNAWATI', 'EVITA', 'FADHEA', 'FARAH', 'FATIMAH',
            'FELYA', 'FIKA', 'FITRIA', 'GENDHIS', 'GHINA', 'HASANAH', 'HASNA', 'HIDAYAH',
            'HILYATUL', 'INDAH', 'INDANA', 'INTAN', 'ISMA', 'ISMY', 'ISNA', 'IZZA',
            'JAZILA', 'JENI', 'JIHAAN', 'JIHAN', 'JUNIAR', 'KARIN', 'KARYNINA', 'KHALIMATUS',
            'KHANZA', 'KHARISA', 'KHOFIDHOTUR', 'KHOLIFAH', 'KHUROTU', 'KHUSNA', 'KINANTIARA',
            'KUMALA', 'LAILI', 'LALITA', 'LAYLA', 'LESTARI', 'LULUK', 'MARSELLA', 'MATSNA',
            'MAULIDA', 'MAULIDYA', 'MAULIDYAH', 'MAULY', 'MAULYA', 'MAZAYA', 'MEI', 'MELINDA',
            'MEYSHA', 'MIFTHAHUL', 'MIRZA', 'MISKA', 'MUTIARATSAQHOLAINI', 'NABILA',
            'NABILAH', 'NAFA', 'NAFIDA', 'NAFIZZATUS', 'NAILA', 'NAILIS', 'NASWA',
            'NASYA', 'NAURA', 'NAWATIRA', 'NAYLA', 'NIKMATUL', 'NINDI', 'NINDYA',
            'NOVI', 'NURI', 'NURUL', 'PAMELA', 'PRATIWI', 'RAHMA', 'RAHMANIA', 'RAHMAWATI',
            'RAJWA', 'RANY', 'RAYYA', 'RESTI', 'RIA', 'RINI', 'RISKA', 'RIZKA',
            'RIZQIYA', 'ROHMAH', 'ROHMATUL', 'ROSELENA', 'SABRINA', 'SAFIRA',
            'SAKHA', 'SEKAR', 'SELFIMALIKHATI', 'SELLYANA', 'SELSA', 'SELVIANA', 'SHABRINA',
            'SHALMA', 'SHOFIA', 'SHOFWATUL', 'SILMA', 'SILVIA', 'STEVANY', 'TATA',
            'TATAOLIVIA', 'THANIA', 'TITANIA', 'ULYA', 'UMDATUR', 'UMMI', 'UMY',
            'USWATUN', 'VIANDRA', 'WULANSARI', 'YUKA', 'ZAHRA', 'ZAKIYA', 'ZANETTA', 
            'ZEBININA', 'ZIVANA', 'ZULAICHA', 'ZULFA', 'ZYAHRA',
            'AISIYAH', 'ALIYANA', 'AMALIYA', 'ANGGELIAWATI', 'ANISSA', 'ARDHANI', 'ARFAH',
            'ASIH', 'ATIQOH', 'ATTI', 'AULYAL', 'AURA', 'AZARIA', 'AZHKIYA', 'CAMILIYA',
            'DAMAYANTI', 'DESI', 'ELISYA', 'ELZIRA', 'FADHILA', 'FADHILLA', 'FATMA',
            'FAUZIA', 'FAUZIYAH', 'FEBRINA', 'FEBRIYANTI', 'FEBRIYANTY', 'FITHRI',
            'FITRIANI', 'FITRIYANI', 'GHOTOAKA', 'GENDHIS', 'HAMILAH', 'HAMZAH', 'HANIFA',
            'HANIFAH', 'HUSNA', 'HUSNIA', 'HUSNIAH', 'INSANIA', 'IRTIYAH', 'IZZA',
            'JAZILA', 'JINAN', 'KAMILAH', 'KARTIKA', 'KASIH', 'KEYSHA', 'KHAMDANI',
            'KHANSA', 'KHASANAH', 'KHOIROTUN', 'KHOIROTUNNISA', 'LAILATUL', 'LAILATUS',
            'LAYLA', 'LIGIYUN', 'MAH LAILA', 'MAHA', 'MAULA', 'MAULIDA', 'MAULIDIYA',
            'MELANI', 'MUZAENAH', 'NABIILAH', 'NADINE', 'NAILATUR', 'NAILI', 'NAJWA',
            'NIKMAH', 'NILA', 'NING', 'NINGSIH', 'NOOR', 'PERTIWI', 'PRATIWI',
            'PURNOMO', 'PURBONINGRUM', 'PUTRIANA', 'QUNAAH', 'RAFIFAH', 'RAHMAWANTI',
            'RAHMAWATI', 'RAMADHAN SHOLIKIN', 'RAMADHINA', 'ROHMAH',
            'ROHMATUL', 'ROHMAWATI', 'SABRINA', 'SA\'DIYAH', 'SAKHI', 'SALMA', 'SALSABIELA',
            'SANJANI', 'SEVIA', 'SHAFIRA', 'SHOLEHAH', 'SYAFIQOH', 'SYAFITRI', 'SYALBIYA',
            'SYASABILA', 'SYAZA', 'WENINGTYASTUTI', 'WIJAYANTI', 'WIRDHATUL', 'WULANDARI',
            'YAMUNA', 'ZHAAFIRA', 'ZIVANA', 'ZULFA'
        ];

        $manualOverrides = [
            'BROMO' => 'L',
            'NUSUKI' => 'L',
            'REVAN TRISTANTO' => 'L',
            'REVI DWI RAHMAWATI' => 'P',
            'RISKA BAGUS MAULANA' => 'L',
            'RIZKY HIDAYAH' => 'L',
            'SATRIO RAFIFULLOH FADHIL' => 'L',
            'SENO EDHI BASKORO' => 'L',
            'SISWANTO' => 'L',
            'YUSUF AZAM NAWAAL' => 'L',
            'ZIKRA IRSYAD' => 'L',
            'ZAIN AFIF ZAINUDIN' => 'L',
            'ZAINAL ARIFIN' => 'L',
            
            // Explicit unresolved list
            'NIZAM DWI RAMADHAN' => 'L',
            'MUHAMAD ZAKI RAXMA' => 'L',
            'RAMIZA SUHAIMA MASHADI' => 'P',
            'NURIS RAMADHANI' => 'L',
            'AHMAD ASRO MUKHSIN NUR' => 'L',
            'MAULIDA AZKA WULANDARI' => 'P',
            'WAHYU PUTRIANA' => 'P',
            'ANISATUL KHOIROT' => 'P',
            'AKHIYATUL ASFIA FITRIANA' => 'P',
            'AHMAD NURUL FAIZ' => 'L',
            'TRI RATNA FIRDAUSY' => 'P',
            'IHSANUL ADLI SOFYAN' => 'L',
            'HILMY MUHAMMAD NUR' => 'L',
            'NUR ALFIN SATRIYOSO' => 'L',
            'MUHAMAD IQBAL HASAN' => 'L',
            'RIZKI LUTFI' => 'L',
            'AINIYATUZ ZAKIYAH' => 'P',
            'NUZULUL RIZKI' => 'L',
            'AZKIA KHAIRATUN NISA\'' => 'P',
            'ALLIZHA AURARACHMA MAHARANI' => 'P',
            'ANDRI SETIAWAN' => 'L',
            'RENDY DWI PRASETYO' => 'L',
            'AHMAD NUR RONZI' => 'L',
            'MUHAMMAD AFIF NOOR HAN IZZUL HAQ' => 'L',
            'ALANZA SIFA ANGGUN PARAMITAH' => 'P',
            'AGUS PURNOMO' => 'L',
            'FAKA SYAFNA \'ANKA GHITOAKA' => 'P',
            'YOGA DWI SETIAWAN' => 'L',
            'ANNISA KHURMATUZ ZAHROH' => 'P',
            'AMANDA BINTANG AYU SETYA' => 'P',
            'RIZKY AULYAL HUSNA' => 'P',
            'RICKY RAMADHANI SETYA PUTRA' => 'L',
            'VIRGI ARYA RAMADHANI' => 'L',
            'ALIFATUL MUZAINAH' => 'P',
            'ANJALINA LU\'LUUL AINI' => 'P',
            'WAHYU AFRIZANSYAH' => 'L',
            'AFINA ANFA`ANA' => 'P',
            'BUNGA RIZKY AMELIA' => 'P',
            'AIMATUN NAFIS QUROTUL AINI' => 'P',
            'BRIAN MAHARDIKA ALFIANSYAH' => 'L',
            'MAULIDYA WAFA CAMILIYA' => 'P',
            'ABDULLAH HUSAIN MUBAROK' => 'L',
            'ANNASYA FADHEELA ANINDITA' => 'P',
            'WAHYU PURBONINGRUM' => 'P',
            'CHOLISA TAZKIYATUN NAWA' => 'P',
            'NUR LAILI AZKA HUSNIA' => 'P',
            'MUHAMMAD NUR KAFI' => 'L',
            'AISA SALSABILLA' => 'P',
            'EKY ARY NUR ILHAM' => 'L',
            'ZYAHRA NAUFAL PRISCILLIA' => 'P',
            'CHELSI JUNIATUN NISA' => 'P',
            'AINIYATUL AZKIYA\'' => 'P',
            'ALFI LAYYINATUS SYIFA' => 'P',
            'HIKMATUL MUSTHOFIA' => 'P',
            'MUHAMAD FADHIL' => 'L',
            'RIZKA BAGUS MAULANA' => 'L',
            'DINDA NEFAL WILDAN' => 'P',
            'MAS NUR RIZQI' => 'L',
            'AINIYATUZZAKIYA' => 'P',
            'WAHYU MAQRIB MUSTOFA' => 'L'
        ];

        foreach ($unspecified as $s) {
            $nameClean = str_replace(chr(194).chr(160), ' ', $s->name);
            $nameClean = str_replace('&nbsp;', ' ', $nameClean);
            $nameClean = trim(preg_replace('/\s+/', ' ', $nameClean));
            $nameCleanUpper = strtoupper($nameClean);
            
            $gender = null;
            
            // Check manual overrides first
            if (isset($manualOverrides[$nameCleanUpper])) {
                $gender = $manualOverrides[$nameCleanUpper];
            } else {
                // Tokenize into words
                $words = preg_split('/[\s,.]+/', $nameCleanUpper);
                
                $isMale = false;
                $isFemale = false;
                
                foreach ($femaleKeywords as $fk) {
                    if (in_array($fk, $words)) {
                        $isFemale = true;
                        break;
                    }
                }
                
                foreach ($maleKeywords as $mk) {
                    if (in_array($mk, $words)) {
                        $isMale = true;
                        break;
                    }
                }
                
                if ($isMale && !$isFemale) {
                    $gender = 'L';
                } elseif ($isFemale && !$isMale) {
                    $gender = 'P';
                }
            }
            
            if ($gender !== null) {
                DB::table('students')
                    ->where('id', $s->id)
                    ->update(['gender' => $gender]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverting data-fixes since they correct unspecified blanks
    }
};
