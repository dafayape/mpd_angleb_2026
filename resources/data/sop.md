  
---

**Standar Operasional Prosedur**  
---

|  |
| ----- |
| **Angkutan Lebaran 2026 Kementerian Perhubungan Republik Indonesia** |
| **versi 1.1** |

**Daftar Isi**

DAFTAR ISI......................................................................................................................2  
1.Pendahuluan ................................................................................................................ 3  
2.Struktur Penamaan File Angleb …............................................................................... 4  
2.1 File Angleb….........................................................................................................4  
3.Struktur folder untuk file Angleb…………………...........................................................4  
4.Struktur File Angleb …………….……............................................................................5  
4.1 File Data Harian Angleb(.csv).......…………………………………………………...…5  
5\. Referensi Lookup Table……………………………………………………………………..6  
5.1 Provinsi…………………………………………………………………………………..6  
5.2 Kabupaten……………………………………………………………………………….7  
5.3 Simpul…………………………………………………………………………………..27  
5.4 Moda……………………………………………………………………………………89

**Angleb 2026**

1. **Pendahuluan**

		Aplikasi Angleb 2026 merupakan sistem aplikasi yang dikembangkan untuk mendukung pelaksanaan pemantauan dan rekapitulasi data pergerakan masyarakat pada masa mudik Hari Raya Idul Fitri tahun 2026\. Periode mudik Lebaran ditandai dengan meningkatnya mobilitas penduduk secara signifikan, sehingga diperlukan suatu mekanisme pengelolaan data yang terintegrasi, akurat, dan dapat diakses secara tepat waktu. Angleb 2026 dirancang sebagai solusi berbasis teknologi informasi untuk mengotomatisasi proses pengumpulan, pengolahan, dan penyajian data pergerakan mudik secara sistematis.

Aplikasi Angleb 2026 melakukan pendeteksian dan pencatatan data pergerakan mudik dengan pendekatan berbasis individu, yang meliputi identifikasi pelaku perjalanan, jenis kendaraan yang digunakan, serta wilayah asal (origin) dan tujuan (destinasi) perjalanan. Data tersebut direkap secara otomatis dan disajikan dalam bentuk informasi yang terstruktur guna mendukung proses analisis dan evaluasi. Dengan implementasi Angleb 2026, diharapkan pemerintah dan instansi terkait dapat memperoleh gambaran yang komprehensif mengenai pola pergerakan mudik masyarakat, sehingga dapat menjadi dasar dalam perencanaan kebijakan, pengambilan keputusan, serta pengendalian arus mudik dan arus balik Lebaran tahun 2026 secara efektif dan efisien.

2. **Struktur File Penamaan Angleb**

**2.1 File Angleb**  
		Berikut adalah file Angleb yang akan di berikan ke pihak kami terdiri dari file Data harian dengan format sebagai berikut.  
**MPD**\_**AAA\_TT\_YYYYMMDD**  
**Keterangan:**   
**MPD		\= mpd label**  
**AAA 		\= Nama  Opsel**  
**TT 		\= Type data (real/forecast)**  
**YYYYMMDD	\= Tanggal Pergerakan**  
**Contoh:**   
Nama opsel IOH, Tipe data Real dan Forecast, 25 Februari 2026 Maka Nama File Data Harian nya adalah: 

* mpd\_ioh\_real\_20260225.csv  
* mpd\_ioh\_forecast\_20260225.csv

keterangan: pastikan struktur penamaan menggunakan huruf non kapital.

3. **Struktur Folder untuk File Angleb**  
   Untuk Proses Penempatan file data harian Angleb 2026 ditetapkan sebagai berikut:   
1. File Disimpan di Drive yang sudah disediakan **“URL Drive”**  
2. Setiap File di simpan di dalam folder berdasarkan tanggal pergerakan nya

contoh nya seperti berikut:

| Angleb 2026/ └── 26.02.25/     └── mpd\_ioh\_real\_20260225.csv     └── mpd\_ioh\_forecast\_20260225.csv |
| :---- |

**4\. Struktur File Angleb**  
**4.1 	File Data Harian Angleb (.csv)**  
FILENAME		: (\*.csv)  
SEND BY 		: Opsel  
TO 			: MITRA  
AS 			: FILE DAILY DATA  
	Adalah File data harian yang sukses yang sudah di filter sesuai dengan format yang menjadi acuan pergerakan selama mudik lebaran. 

1. **Format File**

| No | Column Name | Type | Length | Format | Description |
| :---: | ----- | ----- | ----- | ----- | ----- |
| **1\.** | TANGGAL | string | 10 | YYYY-MM-DD |  |
| **2\.** | OPSEL | string | 4 | AAAA | isi dengan (TSEL,IOH,XL) |
| **3\.** | KATEGORI | string | 10 | KKKKKK | isi dengan (ORANG,PERGERAKAN) |
| **3\.** | KODE\_ORIGIN\_PROVINSI | int | 2 | PP | isi dengan kode provinsi (11,12,13) |
| **4\.**  | ORIGIN\_PROVINSI | string |  |  |  |
| **5\.** | KODE\_ORIGIN\_KABUPATEN\_KOTA | int | 4 | PPKK | isi dengan kode provinsi dan kabupaten/Kota(1101,1102,1103) |
| **6\.** | ORIGIN\_KABUPATEN\_KOTA | string | 35 | AAA \~\~ | isi dengan nama kabupaten/Kota |
| **7\.** | KODE\_DEST\_PROVINSI | string | 2 | PP | isi dengan kode provinsi (11,12,13) |
| **8\.** | DEST\_PROVINSI | string |  |  |  |
| **9\.** | KODE\_DEST\_KABUPATEN\_KOTA | string | 4 | PPKK | isi dengan kode provinsi dan kabupaten/Kota(1101,1102,1103) |
| **10\.** | DEST\_KABUPATEN\_KOTA | string | 35 | AAA \~\~ | isi dengan nama kabupaten/Kota |
| **11\.** | KODE\_ORIGIN\_SIMPUL | string | 255 | KKKKKK \~\~ | isi dengan kode origin simpul yang sudah di tentukan |
| **12\.** | ORIGIN\_SIMPUL | string | 50 | SSS \~\~ | isi dengan nama simpul |
| **13\.** | KODE\_DEST\_SIMPUL | string | 255 | KKKKKK \~\~ | isi dengan kode origin simpul yang sudah di tentukan |
| **14\.** | DEST\_SIMPUL | string | 50  | SSS \~\~ | isi dengan nama simpul |
| **15\.** | KODE\_MODA | char | 1 | K | isi dengan kode moda yang sudah di tentukan |
| **16\.** | MODA | string | 50 | MMM \~\~ | isi dengan nama Moda |
| **17\.** | TOTAL | int | 4 | NNNN | total data |

**\*Pastikan Separator yang digunakan csv adalah Semicolon “;”**

	**5\. Referensi Lookup Table**  
	**5.1 Provinsi**

| NO | KODE | PROVINSI |
| :---: | ----- | ----- |
| **1** | 11 | ACEH |
| **2** | 12 | SUMATERA UTARA |
| **3** | 13 | SUMATERA BARAT |
| **4** | 14 | RIAU |
| **5** | 15 | JAMBI |
| **6** | 16 | SUMATERA SELATAN |
| **7** | 17 | BENGKULU |
| **8** | 18 | LAMPUNG |
| **9** | 19 | KEPULAUAN BANGKA BELITUNG |
| **10** | 21 | KEPULAUAN RIAU |
| **12** | 31 | DKI JAKARTA |
| **13** | 32 | JAWA BARAT |
| **14** | 33 | JAWA TENGAH |
| **15** | 34 | DAERAH ISTIMEWA YOGYAKARTA |
| **16** | 35 | JAWA TIMUR |
| **17** | 36 | BANTEN |
| **18** | 51 | BALI |
| **19** | 52 | NUSA TENGGARA BARAT |
| **20** | 53  | NUSA TENGGARA TIMUR |
| **21** | 61 | KALIMANTAN BARAT |
| **22** | 62 | KALIMANTAN TENGAH |
| **23** | 63 | KALIMANTAN SELATAN |
| **24** | 64 | KALIMANTAN TIMUR |
| **25** | 65 | KALIMANTAN UTARA |
| **26** | 71 | SULAWESI UTARA |
| **27** | 72 | SULAWESI TENGAH |
| **28** | 73 | SULAWESI SELATAN |
| **29** | 74 | SULAWESI TENGGARA |
| **30** | 75 | GORONTALO |
| **31** | 76 | SULAWESI BARAT |
| **32** | 81 | MALUKU |
| **33** | 82 | MALUKU UTARA |
| **34** | 91 | PAPUA |
| **35** | 92 | PAPUA BARAT |
| **36** | 93 | PAPUA SELATAN |
| **37** | 94 | PAPUA TENGAH |
| **38** | 95 | PAPUA PEGUNUNGAN |

	**5.2 Kabupaten/Kota**

| NO | KODE | KABUPATEN \_ KOTA |
| :---: | ----- | ----- |
| **1** | 1101 | KAB. ACEH SELATAN |
| **2** | 1102 | KAB. ACEH TENGGARA |
| **3** | 1103 | KAB. ACEH TIMUR |
| **4** | 1104 | KAB. ACEH TENGAH |
| **5** | 1105 | KAB. ACEH BARAT |
| **6** | 1106 | KAB. ACEH BESAR |
| **7** | 1107 | KAB. PIDIE |
| **8** | 1108 | KAB. ACEH UTARA |
| **9** | 1109 | KAB. SIMEULUE |
| **10** | 1110 | KAB. ACEH SINGKIL |
| **11** | 1111 | KAB. BIREUEN |
| **12** | 1112 | KAB. ACEH BARAT DAYA |
| **13** | 1113 | KAB. GAYO LUES |
| **14** | 1114 | KAB. ACEH JAYA |
| **15** | 1115 | KAB. NAGAN RAYA |
| **16** | 1116 | KAB. ACEH TAMIANG |
| **17** | 1117 | KAB. BENER MERIAH |
| **18** | 1118 | KAB. PIDIE JAYA |
| **19** | 1171 | KOTA BANDA ACEH |
| **20** | 1172 | KOTA SABANG |
| **21** | 1173 | KOTA LHOKSEUMAWE |
| **22** | 1174 | KOTA LANGSA |
| **23** | 1175 | KOTA SUBULUSSALAM |
| **24** | 1201 | KAB. TAPANULI TENGAH |
| **25** | 1202 | KAB. TAPANULI UTARA |
| **26** | 1203 | KAB. TAPANULI SELATAN |
| **27** | 1204 | KAB. NIAS |
| **28** | 1205 | KAB. LANGKAT |
| **29** | 1206 | KAB. KARO |
| **30** | 1207 | KAB. DELI SERDANG |
| **31** | 1208 | KAB. SIMALUNGUN |
| **32** | 1209 | KAB. ASAHAN |
| **33** | 1210 | KAB. LABUHANBATU |
| **34** | 1211 | KAB. DAIRI |
| **35** | 1212 | KAB. TOBA |
| **36** | 1213 | KAB. MANDAILING NATAL |
| **37** | 1214 | KAB. NIAS SELATAN |
| **38** | 1215 | KAB. PAKPAK BHARAT |
| **39** | 1216 | KAB. HUMBANG HASUNDUTAN |
| **40** | 1217 | KAB. SAMOSIR |
| **41** | 1218 | KAB. SERDANG BEDAGAI |
| **42** | 1219 | KAB. BATU BARA |
| **43** | 1220 | KAB. PADANG LAWAS UTARA |
| **44** | 1221 | KAB. PADANG LAWAS |
| **45** | 1222 | KAB. LABUHANBATU SELATAN |
| **46** | 1223 | KAB. LABUHANBATU UTARA |
| **47** | 1224 | KAB. NIAS UTARA |
| **48** | 1225 | KAB. NIAS BARAT |
| **49** | 1271 | KOTA MEDAN |
| **50** | 1272 | KOTA PEMATANGSIANTAR |
| **51** | 1273 | KOTA SIBOLGA |
| **52** | 1274 | KOTA TANJUNG BALAI |
| **53** | 1275 | KOTA BINJAI |
| **54** | 1276 | KOTA TEBING TINGGI |
| **55** | 1277 | KOTA PADANGSIDIMPUAN |
| **56** | 1278 | KOTA GUNUNGSITOLI |
| **57** | 1301 | KAB. PESISIR SELATAN |
| **58** | 1302 | KAB. SOLOK |
| **59** | 1303 | KAB. SIJUNJUNG |
| **60** | 1304 | KAB. TANAH DATAR |
| **61** | 1305 | KAB. PADANG PARIAMAN |
| **62** | 1306 | KAB. AGAM |
| **63** | 1307 | KAB. LIMA PULUH KOTA |
| **64** | 1308 | KAB. PASAMAN |
| **65** | 1309 | KAB. KEPULAUAN MENTAWAI |
| **66** | 1310 | KAB. DHARMASRAYA |
| **67** | 1311 | KAB. SOLOK SELATAN |
| **68** | 1312 | KAB. PASAMAN BARAT |
| **69** | 1371 | KOTA PADANG |
| **70** | 1372 | KOTA SOLOK |
| **71** | 1373 | KOTA SAWAHLUNTO |
| **72** | 1374 | KOTA PADANG PANJANG |
| **73** | 1375 | KOTA BUKITTINGGI |
| **74** | 1376 | KOTA PAYAKUMBUH |
| **75** | 1377 | KOTA PARIAMAN |
| **76** | 1401 | KAB. KAMPAR |
| **77** | 1402 | KAB. INDRAGIRI HULU |
| **78** | 1403 | KAB. BENGKALIS |
| **79** | 1404 | KAB. INDRAGIRI HILIR |
| **80** | 1405 | KAB. PELALAWAN |
| **81** | 1406 | KAB. ROKAN HULU |
| **82** | 1407 | KAB. ROKAN HILIR |
| **83** | 1408 | KAB. SIAK |
| **84** | 1409 | KAB. KUANTAN SINGINGI |
| **85** | 1410 | KAB. KEPULAUAN MERANTI |
| **86** | 1471 | KOTA PEKANBARU |
| **87** | 1472 | KOTA DUMAI |
| **88** | 1501 | KAB. KERINCI |
| **89** | 1502 | KAB. MERANGIN |
| **90** | 1503 | KAB. SAROLANGUN |
| **91** | 1504 | KAB. BATANGHARI |
| **92** | 1505 | KAB. MUARO JAMBI |
| **93** | 1506 | KAB. TANJUNG JABUNG BARAT |
| **94** | 1507 | KAB. TANJUNG JABUNG TIMUR |
| **95** | 1508 | KAB. BUNGO |
| **96** | 1509 | KAB. TEBO |
| **97** | 1571 | KOTA JAMBI |
| **98** | 1572 | KOTA SUNGAI PENUH |
| **99** | 1601 | KAB. OGAN KOMERING ULU |
| **100** | 1602 | KAB. OGAN KOMERING ILIR |
| **101** | 1603 | KAB. MUARA ENIM |
| **102** | 1604 | KAB. LAHAT |
| **103** | 1605 | KAB. MUSI RAWAS |
| **104** | 1606 | KAB. MUSI BANYUASIN |
| **105** | 1607 | KAB. BANYUASIN |
| **106** | 1608 | KAB. OGAN KOMERING ULU TIMUR |
| **107** | 1609 | KAB. OGAN KOMERING ULU SELATAN |
| **108** | 1610 | KAB. OGAN ILIR |
| **109** | 1611 | KAB. EMPAT LAWANG |
| **110** | 1612 | KAB. PENUKAL ABAB LEMATANG ILIR |
| **111** | 1613 | KAB. MUSI RAWAS UTARA |
| **112** | 1671 | KOTA PALEMBANG |
| **113** | 1672 | KOTA PAGAR ALAM |
| **114** | 1673 | KOTA LUBUK LINGGAU |
| **115** | 1674 | KOTA PRABUMULIH |
| **116** | 1701 | KAB. BENGKULU SELATAN |
| **117** | 1702 | KAB. REJANG LEBONG |
| **118** | 1703 | KAB. BENGKULU UTARA |
| **119** | 1704 | KAB. KAUR |
| **120** | 1705 | KAB. SELUMA |
| **121** | 1706 | KAB. MUKO MUKO |
| **122** | 1707 | KAB. LEBONG |
| **123** | 1708 | KAB. KEPAHIANG |
| **124** | 1709 | KAB. BENGKULU TENGAH |
| **125** | 1771 | KOTA BENGKULU |
| **126** | 1801 | KAB. LAMPUNG SELATAN |
| **127** | 1802 | KAB. LAMPUNG TENGAH |
| **128** | 1803 | KAB. LAMPUNG UTARA |
| **129** | 1804 | KAB. LAMPUNG BARAT |
| **130** | 1805 | KAB. TULANG BAWANG |
| **131** | 1806 | KAB. TANGGAMUS |
| **132** | 1807 | KAB. LAMPUNG TIMUR |
| **133** | 1808 | KAB. WAY KANAN |
| **134** | 1809 | KAB. PESAWARAN |
| **135** | 1810 | KAB. PRINGSEWU |
| **136** | 1811 | KAB. MESUJI |
| **137** | 1812 | KAB. TULANG BAWANG BARAT |
| **138** | 1813 | KAB. PESISIR BARAT |
| **139** | 1871 | KOTA BANDAR LAMPUNG |
| **140** | 1872 | KOTA METRO |
| **141** | 1901 | KAB. BANGKA |
| **142** | 1902 | KAB. BELITUNG |
| **143** | 1903 | KAB. BANGKA SELATAN |
| **144** | 1904 | KAB. BANGKA TENGAH |
| **145** | 1905 | KAB. BANGKA BARAT |
| **146** | 1906 | KAB. BELITUNG TIMUR |
| **147** | 1971 | KOTA PANGKAL PINANG |
| **148** | 2101 | KAB. BINTAN |
| **149** | 2102 | KAB. KARIMUN |
| **150** | 2103 | KAB. NATUNA |
| **151** | 2104 | KAB. LINGGA |
| **152** | 2105 | KAB. KEPULAUAN ANAMBAS |
| **153** | 2171 | KOTA BATAM |
| **154** | 2172 | KOTA TANJUNG PINANG |
| **155** | 3101 | KAB. ADM. KEP. SERIBU |
| **156** | 3171 | KOTA ADM. JAKARTA PUSAT |
| **157** | 3172 | KOTA ADM. JAKARTA UTARA |
| **158** | 3173 | KOTA ADM. JAKARTA BARAT |
| **159** | 3174 | KOTA ADM. JAKARTA SELATAN |
| **160** | 3175 | KOTA ADM. JAKARTA TIMUR |
| **161** | 3201 | KAB. BOGOR |
| **162** | 3202 | KAB. SUKABUMI |
| **163** | 3203 | KAB. CIANJUR |
| **164** | 3204 | KAB. BANDUNG |
| **165** | 3205 | KAB. GARUT |
| **166** | 3206 | KAB. TASIKMALAYA |
| **167** | 3207 | KAB. CIAMIS |
| **168** | 3208 | KAB. KUNINGAN |
| **169** | 3209 | KAB. CIREBON |
| **170** | 3210 | KAB. MAJALENGKA |
| **171** | 3211 | KAB. SUMEDANG |
| **172** | 3212 | KAB. INDRAMAYU |
| **173** | 3213 | KAB. SUBANG |
| **174** | 3214 | KAB. PURWAKARTA |
| **175** | 3215 | KAB. KARAWANG |
| **176** | 3216 | KAB. BEKASI |
| **177** | 3217 | KAB. BANDUNG BARAT |
| **178** | 3218 | KAB. PANGANDARAN |
| **179** | 3271 | KOTA BOGOR |
| **180** | 3272 | KOTA SUKABUMI |
| **181** | 3273 | KOTA BANDUNG |
| **182** | 3274 | KOTA CIREBON |
| **183** | 3275 | KOTA BEKASI |
| **184** | 3276 | KOTA DEPOK |
| **185** | 3277 | KOTA CIMAHI |
| **186** | 3278 | KOTA TASIKMALAYA |
| **187** | 3279 | KOTA BANJAR |
| **188** | 3301 | KAB. CILACAP |
| **189** | 3302 | KAB. BANYUMAS |
| **190** | 3303 | KAB. PURBALINGGA |
| **191** | 3304 | KAB. BANJARNEGARA |
| **192** | 3305 | KAB. KEBUMEN |
| **193** | 3306 | KAB. PURWOREJO |
| **194** | 3307 | KAB. WONOSOBO |
| **195** | 3308 | KAB. MAGELANG |
| **196** | 3309 | KAB. BOYOLALI |
| **197** | 3310 | KAB. KLATEN |
| **198** | 3311 | KAB. SUKOHARJO |
| **199** | 3312 | KAB. WONOGIRI |
| **200** | 3313 | KAB. KARANGANYAR |
| **201** | 3314 | KAB. SRAGEN |
| **202** | 3315 | KAB. GROBOGAN |
| **203** | 3316 | KAB. BLORA |
| **204** | 3317 | KAB. REMBANG |
| **205** | 3318 | KAB. PATI |
| **206** | 3319 | KAB. KUDUS |
| **207** | 3320 | KAB. JEPARA |
| **208** | 3321 | KAB. DEMAK |
| **209** | 3322 | KAB. SEMARANG |
| **210** | 3323 | KAB. TEMANGGUNG |
| **211** | 3324 | KAB. KENDAL |
| **212** | 3325 | KAB. BATANG |
| **213** | 3326 | KAB. PEKALONGAN |
| **214** | 3327 | KAB. PEMALANG |
| **215** | 3328 | KAB. TEGAL |
| **216** | 3329 | KAB. BREBES |
| **217** | 3371 | KOTA MAGELANG |
| **218** | 3372 | KOTA SURAKARTA |
| **219** | 3373 | KOTA SALATIGA |
| **220** | 3374 | KOTA SEMARANG |
| **221** | 3375 | KOTA PEKALONGAN |
| **222** | 3376 | KOTA TEGAL |
| **223** | 3401 | KAB. KULON PROGO |
| **224** | 3402 | KAB. BANTUL |
| **225** | 3403 | KAB. GUNUNGKIDUL |
| **226** | 3404 | KAB. SLEMAN |
| **227** | 3471 | KOTA YOGYAKARTA |
| **228** | 3501 | KAB. PACITAN |
| **229** | 3502 | KAB. PONOROGO |
| **230** | 3503 | KAB. TRENGGALEK |
| **231** | 3504 | KAB. TULUNGAGUNG |
| **232** | 3505 | KAB. BLITAR |
| **233** | 3506 | KAB. KEDIRI |
| **234** | 3507 | KAB. MALANG |
| **235** | 3508 | KAB. LUMAJANG |
| **236** | 3509 | KAB. JEMBER |
| **237** | 3510 | KAB. BANYUWANGI |
| **238** | 3511 | KAB. BONDOWOSO |
| **239** | 3512 | KAB. SITUBONDO |
| **240** | 3513 | KAB. PROBOLINGGO |
| **241** | 3514 | KAB. PASURUAN |
| **242** | 3515 | KAB. SIDOARJO |
| **243** | 3516 | KAB. MOJOKERTO |
| **244** | 3517 | KAB. JOMBANG |
| **245** | 3518 | KAB. NGANJUK |
| **246** | 3519 | KAB. MADIUN |
| **247** | 3520 | KAB. MAGETAN |
| **248** | 3521 | KAB. NGAWI |
| **249** | 3522 | KAB. BOJONEGORO |
| **250** | 3523 | KAB. TUBAN |
| **251** | 3524 | KAB. LAMONGAN |
| **252** | 3525 | KAB. GRESIK |
| **253** | 3526 | KAB. BANGKALAN |
| **254** | 3527 | KAB. SAMPANG |
| **255** | 3528 | KAB. PAMEKASAN |
| **256** | 3529 | KAB. SUMENEP |
| **257** | 3571 | KOTA KEDIRI |
| **258** | 3572 | KOTA BLITAR |
| **259** | 3573 | KOTA MALANG |
| **260** | 3574 | KOTA PROBOLINGGO |
| **261** | 3575 | KOTA PASURUAN |
| **262** | 3576 | KOTA MOJOKERTO |
| **263** | 3577 | KOTA MADIUN |
| **264** | 3578 | KOTA SURABAYA |
| **265** | 3579 | KOTA BATU |
| **266** | 3601 | KAB. PANDEGLANG |
| **267** | 3602 | KAB. LEBAK |
| **268** | 3603 | KAB. TANGERANG |
| **269** | 3604 | KAB. SERANG |
| **270** | 3671 | KOTA TANGERANG |
| **271** | 3672 | KOTA CILEGON |
| **272** | 3673 | KOTA SERANG |
| **273** | 3674 | KOTA TANGERANG SELATAN |
| **274** | 5101 | KAB. JEMBRANA |
| **275** | 5102 | KAB. TABANAN |
| **276** | 5103 | KAB. BADUNG |
| **277** | 5104 | KAB. GIANYAR |
| **278** | 5105 | KAB. KLUNGKUNG |
| **279** | 5106 | KAB. BANGLI |
| **280** | 5107 | KAB. KARANGASEM |
| **281** | 5108 | KAB. BULELENG |
| **282** | 5171 | KOTA DENPASAR |
| **283** | 5201 | KAB. LOMBOK BARAT |
| **284** | 5202 | KAB. LOMBOK TENGAH |
| **285** | 5203 | KAB. LOMBOK TIMUR |
| **286** | 5204 | KAB. SUMBAWA |
| **287** | 5205 | KAB. DOMPU |
| **288** | 5206 | KAB. BIMA |
| **289** | 5207 | KAB. SUMBAWA BARAT |
| **290** | 5208 | KAB. LOMBOK UTARA |
| **291** | 5271 | KOTA MATARAM |
| **292** | 5272 | KOTA BIMA |
| **293** | 5301 | KAB. KUPANG |
| **294** | 5302 | KAB TIMOR TENGAH SELATAN |
| **295** | 5303 | KAB. TIMOR TENGAH UTARA |
| **296** | 5304 | KAB. BELU |
| **297** | 5305 | KAB. ALOR |
| **298** | 5306 | KAB. FLORES TIMUR |
| **299** | 5307 | KAB. SIKKA |
| **300** | 5308 | KAB. ENDE |
| **301** | 5309 | KAB. NGADA |
| **302** | 5310 | KAB. MANGGARAI |
| **303** | 5311 | KAB. SUMBA TIMUR |
| **304** | 5312 | KAB. SUMBA BARAT |
| **305** | 5313 | KAB. LEMBATA |
| **306** | 5314 | KAB. ROTE NDAO |
| **307** | 5315 | KAB. MANGGARAI BARAT |
| **308** | 5316 | KAB. NAGEKEO |
| **309** | 5317 | KAB. SUMBA TENGAH |
| **310** | 5318 | KAB. SUMBA BARAT DAYA |
| **311** | 5319 | KAB. MANGGARAI TIMUR |
| **312** | 5320 | KAB. SABU RAIJUA |
| **313** | 5321 | KAB. MALAKA |
| **314** | 5371 | KOTA KUPANG |
| **315** | 6101 | KAB. SAMBAS |
| **316** | 6102 | KAB. MEMPAWAH |
| **317** | 6103 | KAB. SANGGAU |
| **318** | 6104 | KAB. KETAPANG |
| **319** | 6105 | KAB. SINTANG |
| **320** | 6106 | KAB. KAPUAS HULU |
| **321** | 6107 | KAB. BENGKAYANG |
| **322** | 6108 | KAB. LANDAK |
| **323** | 6109 | KAB. SEKADAU |
| **324** | 6110 | KAB. MELAWI |
| **325** | 6111 | KAB. KAYONG UTARA |
| **326** | 6112 | KAB. KUBU RAYA |
| **327** | 6171 | KOTA PONTIANAK |
| **328** | 6172 | KOTA SINGKAWANG |
| **329** | 6201 | KAB. KOTAWARINGIN BARAT |
| **330** | 6202 | KAB. KOTAWARINGIN TIMUR |
| **331** | 6203 | KAB. KAPUAS |
| **332** | 6204 | KAB. BARITO SELATAN |
| **333** | 6205 | KAB. BARITO UTARA |
| **334** | 6206 | KAB. KATINGAN |
| **335** | 6207 | KAB. SERUYAN |
| **336** | 6208 | KAB. SUKAMARA |
| **337** | 6209 | KAB. LAMANDAU |
| **338** | 6210 | KAB. GUNUNG MAS |
| **339** | 6211 | KAB. PULANG PISAU |
| **340** | 6212 | KAB. MURUNG RAYA |
| **341** | 6213 | KAB. BARITO TIMUR |
| **342** | 6271 | KOTA PALANGKARAYA |
| **343** | 6301 | KAB. TANAH LAUT |
| **344** | 6302 | KAB. KOTABARU |
| **345** | 6303 | KAB. BANJAR |
| **346** | 6304 | KAB. BARITO KUALA |
| **347** | 6305 | KAB. TAPIN |
| **348** | 6306 | KAB. HULU SUNGAI SELATAN |
| **349** | 6307 | KAB. HULU SUNGAI TENGAH |
| **350** | 6308 | KAB. HULU SUNGAI UTARA |
| **351** | 6309 | KAB. TABALONG |
| **352** | 6310 | KAB. TANAH BUMBU |
| **353** | 6311 | KAB. BALANGAN |
| **354** | 6371 | KOTA BANJARMASIN |
| **355** | 6372 | KOTA BANJARBARU |
| **356** | 6401 | KAB. PASER |
| **357** | 6402 | KAB. KUTAI KARTANEGARA |
| **358** | 6403 | KAB. BERAU |
| **359** | 6407 | KAB. KUTAI BARAT |
| **360** | 6408 | KAB. KUTAI TIMUR |
| **361** | 6409 | KAB. PENAJAM PASER UTARA |
| **362** | 6411 | KAB. MAHAKAM ULU |
| **363** | 6471 | KOTA BALIKPAPAN |
| **364** | 6472 | KOTA SAMARINDA |
| **365** | 6474 | KOTA BONTANG |
| **366** | 6501 | KAB. BULUNGAN |
| **367** | 6502 | KAB. MALINAU |
| **368** | 6503 | KAB. NUNUKAN |
| **369** | 6504 | KAB. TANA TIDUNG |
| **370** | 6571 | KOTA TARAKAN |
| **371** | 7101 | KAB. BOLAANG MONGONDOW |
| **372** | 7102 | KAB. MINAHASA |
| **373** | 7103 | KAB. KEPULAUAN SANGIHE |
| **374** | 7104 | KAB. KEPULAUAN TALAUD |
| **375** | 7105 | KAB. MINAHASA SELATAN |
| **376** | 7106 | KAB. MINAHASA UTARA |
| **377** | 7107 | KAB. MINAHASA TENGGARA |
| **378** | 7108 | KAB. BOLAANG MONGONDOW UTARA |
| **379** | 7109 | KAB. KEP. SIAU TAGULANDANG BIARO |
| **380** | 7110 | KAB. BOLAANG MONGONDOW TIMUR |
| **381** | 7111 | KAB. BOLAANG MONGONDOW SELATAN |
| **382** | 7171 | KOTA MANADO |
| **383** | 7172 | KOTA BITUNG |
| **384** | 7173 | KOTA TOMOHON |
| **385** | 7174 | KOTA KOTAMOBAGU |
| **386** | 7201 | KAB. BANGGAI |
| **387** | 7202 | KAB. POSO |
| **388** | 7203 | KAB. DONGGALA |
| **389** | 7204 | KAB. TOLI TOLI |
| **390** | 7205 | KAB. BUOL |
| **391** | 7206 | KAB. MOROWALI |
| **392** | 7207 | KAB. BANGGAI KEPULAUAN |
| **393** | 7208 | KAB. PARIGI MOUTONG |
| **394** | 7209 | KAB. TOJO UNA UNA |
| **395** | 7210 | KAB. SIGI |
| **396** | 7211 | KAB. BANGGAI LAUT |
| **397** | 7212 | KAB. MOROWALI UTARA |
| **398** | 7271 | KOTA PALU |
| **399** | 7301 | KAB. KEPULAUAN SELAYAR |
| **400** | 7302 | KAB. BULUKUMBA |
| **401** | 7303 | KAB. BANTAENG |
| **402** | 7304 | KAB. JENEPONTO |
| **403** | 7305 | KAB. TAKALAR |
| **404** | 7306 | KAB. GOWA |
| **405** | 7307 | KAB. SINJAI |
| **406** | 7308 | KAB. BONE |
| **407** | 7309 | KAB. MAROS |
| **408** | 7310 | KAB. PANGKAJENE KEPULAUAN |
| **409** | 7311 | KAB. BARRU |
| **410** | 7312 | KAB. SOPPENG |
| **411** | 7313 | KAB. WAJO |
| **412** | 7314 | KAB. SIDENRENG RAPPANG |
| **413** | 7315 | KAB. PINRANG |
| **414** | 7316 | KAB. ENREKANG |
| **415** | 7317 | KAB. LUWU |
| **416** | 7318 | KAB. TANA TORAJA |
| **417** | 7322 | KAB. LUWU UTARA |
| **418** | 7324 | KAB. LUWU TIMUR |
| **419** | 7326 | KAB. TORAJA UTARA |
| **420** | 7371 | KOTA MAKASSAR |
| **421** | 7372 | KOTA PARE PARE |
| **422** | 7373 | KOTA PALOPO |
| **423** | 7401 | KAB. KOLAKA |
| **424** | 7402 | KAB. KONAWE |
| **425** | 7403 | KAB. MUNA |
| **426** | 7404 | KAB. BUTON |
| **427** | 7405 | KAB. KONAWE SELATAN |
| **428** | 7406 | KAB. BOMBANA |
| **429** | 7407 | KAB. WAKATOBI |
| **430** | 7408 | KAB. KOLAKA UTARA |
| **431** | 7409 | KAB. KONAWE UTARA |
| **432** | 7410 | KAB. BUTON UTARA |
| **433** | 7411 | KAB. KOLAKA TIMUR |
| **434** | 7412 | KAB. KONAWE KEPULAUAN |
| **435** | 7413 | KAB. MUNA BARAT |
| **436** | 7414 | KAB. BUTON TENGAH |
| **437** | 7415 | KAB. BUTON SELATAN |
| **438** | 7471 | KOTA KENDARI |
| **439** | 7472 | KOTA BAU BAU |
| **440** | 7501 | KAB. GORONTALO |
| **441** | 7502 | KAB. BOALEMO |
| **442** | 7503 | KAB. BONE BOLANGO |
| **443** | 7504 | KAB. POHUWATO |
| **444** | 7505 | KAB. GORONTALO UTARA |
| **445** | 7571 | KOTA GORONTALO |
| **446** | 7601 | KAB. PASANGKAYU |
| **447** | 7602 | KAB. MAMUJU |
| **448** | 7603 | KAB. MAMASA |
| **449** | 7604 | KAB. POLEWALI MANDAR |
| **450** | 7605 | KAB. MAJENE |
| **451** | 7606 | KAB. MAMUJU TENGAH |
| **452** | 8101 | KAB. MALUKU TENGAH |
| **453** | 8102 | KAB. MALUKU TENGGARA |
| **454** | 8103 | KAB. KEPULAUAN TANIMBAR |
| **455** | 8104 | KAB. BURU |
| **456** | 8105 | KAB. SERAM BAGIAN TIMUR |
| **457** | 8106 | KAB. SERAM BAGIAN BARAT |
| **458** | 8107 | KAB. KEPULAUAN ARU |
| **459** | 8108 | KAB. MALUKU BARAT DAYA |
| **460** | 8109 | KAB. BURU SELATAN |
| **461** | 8171 | KOTA AMBON |
| **462** | 8172 | KOTA TUAL |
| **463** | 8201 | KAB. HALMAHERA BARAT |
| **464** | 8202 | KAB. HALMAHERA TENGAH |
| **465** | 8203 | KAB. HALMAHERA UTARA |
| **466** | 8204 | KAB. HALMAHERA SELATAN |
| **467** | 8205 | KAB. KEPULAUAN SULA |
| **468** | 8206 | KAB. HALMAHERA TIMUR |
| **469** | 8207 | KAB. PULAU MOROTAI |
| **470** | 8208 | KAB. PULAU TALIABU |
| **471** | 8271 | KOTA TERNATE |
| **472** | 8272 | KOTA TIDORE KEPULAUAN |
| **473** | 9103 | KAB. JAYAPURA |
| **474** | 9105 | KAB. KEPULAUAN YAPEN |
| **475** | 9106 | KAB. BIAK NUMFOR |
| **476** | 9110 | KAB. SARMI |
| **477** | 9111 | KAB. KEEROM |
| **478** | 9115 | KAB. WAROPEN |
| **479** | 9119 | KAB. SUPIORI |
| **480** | 9120 | KAB. MAMBERAMO RAYA |
| **481** | 9171 | KOTA JAYAPURA |
| **482** | 9201 | KAB. SORONG |
| **483** | 9202 | KAB. MANOKWARI |
| **484** | 9203 | KAB. FAK FAK |
| **485** | 9204 | KAB. SORONG SELATAN |
| **486** | 9205 | KAB. RAJA AMPAT |
| **487** | 9206 | KAB. TELUK BINTUNI |
| **488** | 9207 | KAB. TELUK WONDAMA |
| **489** | 9208 | KAB. KAIMANA |
| **490** | 9209 | KAB. TAMBRAUW |
| **491** | 9210 | KAB. MAYBRAT |
| **492** | 9211 | KAB. MANOKWARI SELATAN |
| **493** | 9212 | KAB. PEGUNUNGAN ARFAK |
| **494** | 9271 | KOTA SORONG |
| **495** | 9301 | KAB. MERAUKE |
| **496** | 9302 | KAB. BOVEN DIGOEL |
| **497** | 9303 | KAB. MAPPI |
| **498** | 9304 | KAB. ASMAT |
| **499** | 9401 | KAB. NABIRE |
| **500** | 9402 | KAB. PUNCAK JAYA |
| **501** | 9403 | KAB. PANIAI |
| **502** | 9404 | KAB. MIMIKA |
| **503** | 9405 | KAB. PUNCAK |
| **504** | 9406 | KAB. DOGIYAI |
| **505** | 9407 | KAB. INTAN JAYA |
| **506** | 9408 | KAB. DEIYAI |
| **507** | 9501 | KAB. JAYAWIJAYA |
| **508** | 9502 | KAB. PEGUNUNGAN BINTANG |
| **509** | 9503 | KAB. YAHUKIMO |
| **510** | 9504 | KAB. TOLIKARA |
| **511** | 9505 | KAB. MAMBERAMO TENGAH |
| **512** | 9506 | KAB. YALIMO |
| **513** | 9507 | KAB. LANNY JAYA |
| **514** | 9508 | KAB. NDUGA |

	**5.3 Simpul**

| NO | KODE SIMPUL | NAMA SIMPUL | KATEGORI SIMPUL |
| ----- | ----- | ----- | ----- |
| **1** | BKS | Fatmawati Soekarno | Bandara |
| **2** | LKA | Gewayantana | Bandara |
| **3** | BPN | Sultan Aji Muhammad Sulaiman Sepinggan | Bandara |
| **4** | LKI | Lasikin | Bandara |
| **5** | LKM | Bolaang Mongondow | Bandara |
| **6** | SUP | Trunojoyo | Bandara |
| **7** | BMU | Sultan M. Salahuddin | Bandara |
| **8** | LLJ | Silampari | Bandara |
| **9** | LLN | Kelila | Bandara |
| **10** | LLO | Lagaligo | Bandara |
| **11** | SWQ | Sultan Muhammad Kaharuddin | Bandara |
| **12** | LII | Mulia | Bandara |
| **13** | SXK | Mathilda Batlayeri | Bandara |
| **14** | LNU | Kol. Robert Atty Bessing | Bandara |
| **15** | LOP | Zainuddin Abdul Madjid | Bandara |
| **16** | BJW | Soa | Bandara |
| **17** | RAQ | Sugimanuru | Bandara |
| **18** | LPU | Long Apung | Bandara |
| **19** | LRC | Larat | Bandara |
| **20** | LSR | Alas Lauser | Bandara |
| **21** | LSR | Lasondre | Bandara |
| **22** | BTJ | Sultan Iskandar Muda | Bandara |
| **23** | KXB | Sangia Nibandera | Bandara |
| **24** | BUW | Betoambari | Bandara |
| **25** | BUU | Muara Bungo | Bandara |
| **26** | RJM | Marinda | Bandara |
| **27** | BUI | Bokondiri | Bandara |
| **28** | SQG | Tebelian | Bandara |
| **29** | SQN | Emalamo | Bandara |
| **30** | BTW | Bersujud | Bandara |
| **31** | LAH | Oesman Sadik | Bandara |
| **32** | LSW | Malikussaleh | Bandara |
| **33** | SRG | Jenderal Ahmad Yani | Bandara |
| **34** | BTH | Hang Nadim | Bandara |
| **35** | LBJ | Komodo | Bandara |
| **36** | RGT | Japura | Bandara |
| **37** | RGT | Frans Sales Lega | Bandara |
| **38** | LBW | Yuvai Semaring | Bandara |
| **39** | RDE | Merdey | Bandara |
| **40** | SUB | Juanda | Bandara |
| **41** | BRG | Siau | Bandara |
| **42** | Bandara\_Muara Wahau | Muara Wahau | Bandara |
| **43** | Bandara\_Taiyeve | Taiyeve | Bandara |
| **44** | Bandara\_Syekh Hamzah Fansuri | Syekh Hamzah Fansuri | Bandara |
| **45** | Bandara\_Sumarorong | Sumarorong | Bandara |
| **46** | Bandara\_Seko | Seko | Bandara |
| **47** | Bandara\_Rampi | Rampi | Bandara |
| **48** | Bandara\_Pusako Anak Nagari | Pusako Anak Nagari | Bandara |
| **49** | Bandara\_Potowai Butu | Potowai Butu | Bandara |
| **50** | Bandara\_Paro | Paro | Bandara |
| **51** | Bandara\_Mugi | Mugi | Bandara |
| **52** | Bandara\_Tempuling | Tempuling | Bandara |
| **53** | Bandara\_Molof | Molof | Bandara |
| **54** | Bandara\_Maulana Prins Mandapar | Maulana Prins Mandapar | Bandara |
| **55** | Bandara\_Mapanduma | Mapanduma | Bandara |
| **56** | Bandara\_Manggelum | Manggelum | Bandara |
| **57** | Bandara\_Long Ayu | Long Ayu | Bandara |
| **58** | Bandara\_Koroway Batu | Koroway Batu | Bandara |
| **59** | Bandara\_Kobakma | Kobakma | Bandara |
| **60** | Bandara\_Kiwirok | Kiwirok | Bandara |
| **61** | Bandara\_Kenyam | Kenyam | Bandara |
| **62** | BDO | Husein Sastranegara | Bandara |
| **63** | PXA | Atung Bungsu | Bandara |
| **64** | BIK | Frans Kaisiepo | Bandara |
| **65** | PWX | Panua Pohuwato | Bandara |
| **66** | LUV | Karel Sadsuitubun | Bandara |
| **67** | LUW | Syukuran Aminuddin Amir | Bandara |
| **68** | LWE | Wunopito | Bandara |
| **69** | TBM | Tumbang Samba | Bandara |
| **70** | PWL | Jenderal Besar Soedirman | Bandara |
| **71** | BEJ | Kalimarau | Bandara |
| **72** | BWX | Banyuwangi | Bandara |
| **73** | BDJ | Syamsudin Noor | Bandara |
| **74** | TBX | Tambelan | Bandara |
| **75** | MDC | Sam Ratulangi | Bandara |
| **76** | PDG | Minangkabau | Bandara |
| **77** | Bandara\_Yaniruma | Yaniruma | Bandara |
| **78** | Bandara\_Werur | Werur | Bandara |
| **79** | Bandara\_Wangbe | Wangbe | Bandara |
| **80** | Bandara\_Tsinga | Tsinga | Bandara |
| **81** | Bandara\_Teraplu | Teraplu | Bandara |
| **82** | KBU | Gusti Sjamsir Alam | Bandara |
| **83** | SKJ | Singkawang | Bandara |
| **84** | GEB | Gebe | Bandara |
| **85** | KAZ | Kuabang | Bandara |
| **86** | KBF | Karubaga | Bandara |
| **87** | FOO | Numfor | Bandara |
| **88** | FLZ | Dr Ferdinand Lumban Tobing | Bandara |
| **89** | FKQ | Siboru | Bandara |
| **90** | EWI | Enarotali | Bandara |
| **91** | EWE | Ewer | Bandara |
| **92** | ENG | Enggano | Bandara |
| **93** | GHS | Melalan Melak | Bandara |
| **94** | ENE | H. Hasan Aroeboesman | Bandara |
| **95** | ELR | Elelim | Bandara |
| **96** | KBX | Kambuaya | Bandara |
| **97** | KCS | Kamur | Bandara |
| **98** | DTD | Datah Dawai | Bandara |
| **99** | DTB | Raja Sisingamangaraja XII | Bandara |
| **100** | KDI | Haluoleo | Bandara |
| **101** | DRH | Dabra | Bandara |
| **102** | DPS | I Gusti Ngurah Rai | Bandara |
| **103** | GYO | Blangkejeren | Bandara |
| **104** | IMU | Akimuga | Bandara |
| **105** | INX | Inanwatan | Bandara |
| **106** | ILA | Ilaga | Bandara |
| **107** | IAX | Miangas | Bandara |
| **108** | IUL | Illu | Bandara |
| **109** | HMS | Haji Muhammad Sidik | Bandara |
| **110** | HLP | Halim Perdana Kusuma | Bandara |
| **111** | SIQ | Dabo | Bandara |
| **112** | JBB | Noto Hadinegoro | Bandara |
| **113** | DOB | Dobo | Bandara |
| **114** | GXM | Kuala Kurun | Bandara |
| **115** | SIW | Sibisa | Bandara |
| **116** | JHN | Jenderal Besar Abdul Haris Nasution | Bandara |
| **117** | GTO | Djalaluddin | Bandara |
| **118** | JIO | Jos Orno Imsula | Bandara |
| **119** | SEH | Senggeh | Bandara |
| **120** | JOG | Adi Sutjipto | Bandara |
| **121** | GNS | Binaka | Bandara |
| **122** | GLX | Gamar Malamo | Bandara |
| **123** | RTI | David Constantijn Saudale | Bandara |
| **124** | KOX | Kokonao | Bandara |
| **125** | KRC | Depati Parbo | Bandara |
| **126** | CJN | Nusawiru | Bandara |
| **127** | RUF | Yuruf | Bandara |
| **128** | KSR | H. Aroepala | Bandara |
| **129** | CGK | Soekarno Hatta | Bandara |
| **130** | RTU | Maratua | Bandara |
| **131** | KTG | Rahadi Oesman | Bandara |
| **132** | RTO | Budiarto | Bandara |
| **133** | KOE | El Tari | Bandara |
| **134** | SOC | Adi Soemarmo | Bandara |
| **135** | CBN | Cakrabhuwana | Bandara |
| **136** | RKI | Mentawai | Bandara |
| **137** | SOQ | Domine Eduard Osok | Bandara |
| **138** | BXW | Harun Thohir | Bandara |
| **139** | BXM | Batom | Bandara |
| **140** | KWB | Dewadaru | Bandara |
| **141** | BXD | Bade | Bandara |
| **142** | BXB | Babo | Bandara |
| **143** | CXP | Tunggul Wulung | Bandara |
| **144** | KEI | Kepi | Bandara |
| **145** | KEQ | Kebar | Bandara |
| **146** | KFR | Kufar | Bandara |
| **147** | DJJ | Sentani | Bandara |
| **148** | DJB | Sultan Thaha | Bandara |
| **149** | PPR | Tuanku Tambusai | Bandara |
| **150** | DHX | Dhoho | Bandara |
| **151** | DEX | Nop Goliat Dekai | Bandara |
| **152** | SMQ | H. Asan | Bandara |
| **153** | LMU | Letung | Bandara |
| **154** | SBG | Maimun Saleh | Bandara |
| **155** | KJT | Kertajati | Bandara |
| **156** | KJX | Kuala Batu | Bandara |
| **157** | SAU | Tardamu | Bandara |
| **158** | KLP | Kuala Pembuang | Bandara |
| **159** | KMM | Kimaam | Bandara |
| **160** | KNG | Utarom | Bandara |
| **161** | CPF | Ngloram | Bandara |
| **162** | KNO | Kualanamu | Bandara |
| **163** | TMH | Tanah Merah | Bandara |
| **164** | NKD | Sinak | Bandara |
| **165** | WMX | Wamena | Bandara |
| **166** | TPK | Teuku Cut Ali | Bandara |
| **167** | MPC | Mukomuko | Bandara |
| **168** | WNI | Matahora | Bandara |
| **169** | NNX | Nunukan | Bandara |
| **170** | TNJ | Raja Haji Fisabilillah | Bandara |
| **171** | AYW | Ayawasi | Bandara |
| **172** | AXO | Kabir | Bandara |
| **173** | NPO | Nangapinoh | Bandara |
| **174** | MOH | Morowali | Bandara |
| **175** | TMY | Tiom | Bandara |
| **176** | NRE | Namrole | Bandara |
| **177** | NTI | Bintuni | Bandara |
| **178** | MOF | Fransiskus Xaverius Seda | Bandara |
| **179** | PNK | Supadio | Bandara |
| **180** | TMC | Lede Kalumbang | Bandara |
| **181** | MNA | Melonguane | Bandara |
| **182** | PKY | Tjilik Riwut | Bandara |
| **183** | WSR | Wasior | Bandara |
| **184** | ARD | Mali | Bandara |
| **185** | TLI | Sultan Bantilan | Bandara |
| **186** | APD | Arung Palakka | Bandara |
| **187** | TKG | Radin Inten II | Bandara |
| **188** | MLG | Abdulrachman Saleh | Bandara |
| **189** | WTX | Gatot Soebroto | Bandara |
| **190** | TJS | Tanjung Harapan | Bandara |
| **191** | AMQ | Pattimura | Bandara |
| **192** | TJQ | H. A. S. Hanandjoeddin | Bandara |
| **193** | TJG | Tanjung Warukin | Bandara |
| **194** | MWS | Indonesia Morowali Industrial Park | Bandara |
| **195** | UPG | Sultan Hasanuddin | Bandara |
| **196** | OKL | Oksibil | Bandara |
| **197** | WBA | Wahai | Bandara |
| **198** | UOL | Podogul | Bandara |
| **199** | NBX | Nabire | Bandara |
| **200** | OJU | Tanjung Api | Bandara |
| **201** | NAM | Namniwel | Bandara |
| **202** | NAH | Naha | Bandara |
| **203** | NDA | Bandaneira | Bandara |
| **204** | UGU | Bilorai | Bandara |
| **205** | MXB | Andi Jemma | Bandara |
| **206** | WET | Waghete | Bandara |
| **207** | TXM | Teminabuan | Bandara |
| **208** | TXE | Rembele | Bandara |
| **209** | Bandara\_Kabare | Kabare | Bandara |
| **210** | OKQ | Okaba | Bandara |
| **211** | ONI | Moanamani | Bandara |
| **212** | PGQ | Buli | Bandara |
| **213** | WGP | Umbu Mehang Kunda | Bandara |
| **214** | PKN | Iskandar | Bandara |
| **215** | PLW | Mutiara Sis Al Jufri | Bandara |
| **216** | TTE | Sultan Babullah | Bandara |
| **217** | TSY | Wiriadinata | Bandara |
| **218** | PGK | Depati Amir | Bandara |
| **219** | OTI | Pitu | Bandara |
| **220** | TRT | Toraja | Bandara |
| **221** | NTX | Ranai | Bandara |
| **222** | PKU | Sultan Syarif Kasim II | Bandara |
| **223** | TRK | Juwata | Bandara |
| **224** | Bandara\_Bilai | Bilai | Bandara |
| **225** | Bandar\_Aboyaga | Aboyaga | Bandara |
| **226** | MKQ | Mopah | Bandara |
| **227** | ZRM | Mararena | Bandara |
| **228** | Bandara\_Borme | Borme | Bandara |
| **229** | AAP | Aji Pangeran Tumenggung Pranoto | Bandara |
| **230** | Bandara\_Bomakia | Bomakia | Bandara |
| **231** | Bandara\_Kebo | Kebo | Bandara |
| **232** | Bandara\_Binuang | Binuang | Bandara |
| **233** | Bandara\_Faowi | Faowi | Bandara |
| **234** | YIA | Kulon Progo | Bandara |
| **235** | TFY | Muhammad Taufiq Kiemas | Bandara |
| **236** | PLM | S. M. Badaruddin II | Bandara |
| **237** | Bandara\_Beoga | Beoga | Bandara |
| **238** | AHI | Amahai | Bandara |
| **239** | Bandara\_Alama | Alama | Bandara |
| **240** | ZRI | Stevanus Rumbewas | Bandara |
| **241** | Bandara\_Aboy | Aboy | Bandara |
| **242** | TJB | Raja Haji Abdullah | Bandara |
| **243** | MDP | Mindiptanah | Bandara |
| **244** | AEG | Aek Godang | Bandara |
| **245** | PSU | Pangsuma | Bandara |
| **246** | ZEG | Senggo | Bandara |
| **247** | MKW | Rendani | Bandara |
| **248** | Bandara\_John Becker | John Becker | Bandara |
| **249** | AAS | Apalapsili | Bandara |
| **250** | AGD | Anggi | Bandara |
| **251** | MJU | Tampa Padang | Bandara |
| **252** | TIM | Mozez Kilangin | Bandara |
| **253** | ABU | A. A. Bere Tallo | Bandara |
| **254** | Bandara\_Jita | Jita | Bandara |
| **255** | MEQ | Cut Nyak Dhien | Bandara |
| **256** | PSJ | Kasiguncu | Bandara |
| **257** | Bandara\_Jila | Jila | Bandara |
| **258** | KGT | KARANGJATI | KERETA |
| **259** | SGS | SINGOSARI | KERETA |
| **260** | KI | KURAITAJI | KERETA |
| **261** | SGU | SURABAYA GUBENG | KERETA |
| **262** | KEN | KRENCENG | KERETA |
| **263** | NGW | NGAWI | KERETA |
| **264** | SG | SERANG | KERETA |
| **265** | NS20 FTM | MRT \- FATMAWATI INDOMARET | KERETA |
| **266** | KGG | KETANGGUNGAN | KERETA |
| **267** | NS19 CPR | MRT \- CIPETE RAYA | KERETA |
| **268** | NJ | NGANJUK | KERETA |
| **269** | JAB | JAKABARING | KERETA |
| **270** | NJG | NGUJANG | KERETA |
| **271** | SBJ | SEI BEJANGKAR | KERETA |
| **272** | KM | KEBUMEN | KERETA |
| **273** | NS21 LBB | MRT \- LEBAK BULUS GRAB | KERETA |
| **274** | S-05 EQS | LRTJ \- EQUESTRIAN | KERETA |
| **275** | S-06 VEL | LRTJ \- VELODROME | KERETA |
| **276** | NB | NGEBRUK | KERETA |
| **277** | KNN | KRADENAN | KERETA |
| **278** | KNM | KUALANAMU | KERETA |
| **279** | S16 | KCJB \- HALIM | KERETA |
| **280** | NBO | NGROMBO | KERETA |
| **281** | S20 | KCJB \- KARAWANG | KERETA |
| **282** | KMT | KRAMAT | KERETA |
| **283** | KMO | KEMAYORAN | KERETA |
| **284** | KIS | KISARAN | KERETA |
| **285** | KLT | KALISAT | KERETA |
| **286** | KLI | KLARI | KERETA |
| **287** | KLDB | KLENDER BARU | KERETA |
| **288** | KLD | KLENDER | KERETA |
| **289** | KK | KLAKAH | KERETA |
| **290** | SB | SURABAYA KOTA | KERETA |
| **291** | NDL | NGADILUWIH | KERETA |
| **292** | PPR | PAPAR | KERETA |
| **293** | NG | NAGREG | KERETA |
| **294** | NGN | NEGERIAGUNG | KERETA |
| **295** | SDI | SEDADI | KERETA |
| **296** | KBR | KALIBARU | KERETA |
| **297** | JNG | JATINEGARA | KERETA |
| **298** | NS15 SSM | MRT \- ASEAN | KERETA |
| **299** | JR | JEMBER | KERETA |
| **300** | KBL | KUTABLANG | KERETA |
| **301** | SCN | SICINCIN | KERETA |
| **302** | NS10 DKA | MRT \- DUKUH ATAS BNI | KERETA |
| **303** | NS11 STB | MRT \- SETIABUDI ASTRA | KERETA |
| **304** | KBG | KUALA BINGEI | KERETA |
| **305** | NS12 BNH | MRT \- BENDUNGAN HILIR | KERETA |
| **306** | JRL | JERUKLEGI | KERETA |
| **307** | KB | KOTABUMI | KERETA |
| **308** | SDA | SIDOARJO | KERETA |
| **309** | KAT | KARET | KERETA |
| **310** | KBS | KEBASEN | KERETA |
| **311** | KAM | KAMPUNG RAMBUTAN | KERETA |
| **312** | SDM | SUDIMARA | KERETA |
| **313** | JTB | JATIBARANG | KERETA |
| **314** | KAC | KIARACONDONG | KERETA |
| **315** | KA | KARANGANYAR | KERETA |
| **316** | SDR | SIDAREJA | KERETA |
| **317** | JTM | JATI MULYA | KERETA |
| **318** | SDT | SIDOTOPO | KERETA |
| **319** | JTR | JATIROTO | KERETA |
| **320** | JUA | JUANDA | KERETA |
| **321** | NS14 SNY | MRT \- SENAYAN | KERETA |
| **322** | NS13 IST | MRT \- ISTORA MANDIRI | KERETA |
| **323** | JTU | JATIBENING BARU | KERETA |
| **324** | KDH | KEDUNGGEDEH | KERETA |
| **325** | KEJ | KEDUNGJATI | KERETA |
| **326** | JAY | JAYAKARTA | KERETA |
| **327** | SBI | SURABAYA PASAR TURI | KERETA |
| **328** | KE | KARANGTENGAH | KERETA |
| **329** | JBN | JAMBON | KERETA |
| **330** | SBL | SUMBERGEMPOL | KERETA |
| **331** | KDS | KALIDERES | KERETA |
| **332** | SBO | SUMOBITO | KERETA |
| **333** | NMO | NAMBO | KERETA |
| **334** | KDO | KADIPIRO | KERETA |
| **335** | KDN | KEDINDING | KERETA |
| **336** | SBP | SUMBERPUCUNG | KERETA |
| **337** | JBU | JAMBUBARU | KERETA |
| **338** | JAKK | JAKARTA KOTA | KERETA |
| **339** | KDA | KANDANGAN | KERETA |
| **340** | JG | JOMBANG | KERETA |
| **341** | NS18 HJN | MRT \- HAJI NAWI | KERETA |
| **342** | KD | KEDIRI | KERETA |
| **343** | KBY | KEBAYORAN | KERETA |
| **344** | NS17 BLA | MRT \- BLOK A | KERETA |
| **345** | SET | SETIABUDI | KERETA |
| **346** | NS16 BLM | MRT \- BLOK M BCA | KERETA |
| **347** | JMU | JURANGMANGU | KERETA |
| **348** | NRR | NEGARA RATU | KERETA |
| **349** | NRS | NARAS | KERETA |
| **350** | JN | JENAR | KERETA |
| **351** | NS09 BHI | MRT \- BUNDARAN HI | KERETA |
| **352** | PRP | PARUNGPANJANG | KERETA |
| **353** | PC | PUCUK | KERETA |
| **354** | LNA | LENTENG AGUNG | KERETA |
| **355** | LO | LEUWIGOONG | KERETA |
| **356** | LP | LAMPEGAN | KERETA |
| **357** | PRK | PARUNG KUDA | KERETA |
| **358** | LPN | LEMPUYANGAN | KERETA |
| **359** | ML | MALANG | KERETA |
| **360** | RAP | RANTAUPRAPAT | KERETA |
| **361** | MLK | MALANG KOTA LAMA | KERETA |
| **362** | LSR | LOSARI | KERETA |
| **363** | PYK | PAYAKABUNG | KERETA |
| **364** | PRU | PASAR USANG | KERETA |
| **365** | LT | LAHAT | KERETA |
| **366** | LTD | LAUT TADOR | KERETA |
| **367** | PS | PASURUAN | KERETA |
| **368** | PWT | PURWOKERTO | KERETA |
| **369** | LW | LAWANG | KERETA |
| **370** | NT | NGUNUT | KERETA |
| **371** | PDL | PADALARANG | KERETA |
| **372** | LMP | LIMAPULUH | KERETA |
| **373** | LMG | LAMONGAN | KERETA |
| **374** | LMB | LEMAHABANG | KERETA |
| **375** | RAS | RASUNA SAID | KERETA |
| **376** | LMA | PAUHLIMA | KERETA |
| **377** | RBG | RANDUBLATUNG | KERETA |
| **378** | PRA | PERLANAAN | KERETA |
| **379** | PR | PORONG | KERETA |
| **380** | PBR | PRABUMULIH BARU | KERETA |
| **381** | MN | MADIUN | KERETA |
| **382** | LLG | LUBUK LINGGAU | KERETA |
| **383** | LL | LELES | KERETA |
| **384** | PBM | PRABUMULIH | KERETA |
| **385** | MBU | MARBAU | KERETA |
| **386** | ME | MUARA ENIM | KERETA |
| **387** | PD | PADANG | KERETA |
| **388** | MER | MERAK | KERETA |
| **389** | PSMB | PASAR MINGGU BARU | KERETA |
| **390** | MGB | MANGGA BESAR | KERETA |
| **391** | PSM | PASAR MINGGU | KERETA |
| **392** | MGU | MANGILU | KERETA |
| **393** | MGW | MAGUWO | KERETA |
| **394** | MDN | MEDAN | KERETA |
| **395** | MDL | MANDALLE | KERETA |
| **396** | PD | KOTA PADANG | KERETA |
| **397** | MDI | MANDAI | KERETA |
| **398** | PDJ | PONDOKRANJI | KERETA |
| **399** | PSJ | PASIRJENGKOL | KERETA |
| **400** | PTR | PETERONGAN | KERETA |
| **401** | LKK | LABAKKANG | KERETA |
| **402** | MJ | MAJA | KERETA |
| **403** | PUB | PULUBRAYAN | KERETA |
| **404** | MBM | MAMBANGMUDA | KERETA |
| **405** | PUK | PUNTI KAYU | KERETA |
| **406** | PDL | KCJB \- PADALARANG | KERETA |
| **407** | MAG | MAGETAN | KERETA |
| **408** | MA | MAOS | KERETA |
| **409** | PUR | PULURAJA | KERETA |
| **410** | PWA | PURWOASRI | KERETA |
| **411** | PWK | PURWAKARTA | KERETA |
| **412** | PSI | PAKISAJI | KERETA |
| **413** | PSG | PESING | KERETA |
| **414** | PSE | PASARSENEN | KERETA |
| **415** | PWS | PURWOSARI | KERETA |
| **416** | MKL | MUARO KALABAN | KERETA |
| **417** | KTN | KAYUTANAM | KERETA |
| **418** | KSB | KESAMBEN | KERETA |
| **419** | KSL | KALISETAIL | KERETA |
| **420** | KSO | KARANGSONO | KERETA |
| **421** | RU | RAWA BUNTU | KERETA |
| **422** | KT | KLATEN | KERETA |
| **423** | KTA | KUTOARJO | KERETA |
| **424** | KTG | KETAPANG | KERETA |
| **425** | RSU | RSUD | KERETA |
| **426** | KRS | KRAS | KERETA |
| **427** | RPH | RAMPAH | KERETA |
| **428** | KTS | KERTOSONO | KERETA |
| **429** | KUA | KUNINGAN | KERETA |
| **430** | RMG | RAMMANG RAMMANG | KERETA |
| **431** | MTR | MATRAMAN | KERETA |
| **432** | MTM | METLAND TELAGA MURNI | KERETA |
| **433** | KW | KARAWANG | KERETA |
| **434** | MSI | MASWATI | KERETA |
| **435** | KRR | KARANGSARI | KERETA |
| **436** | KRN | KRIAN | KERETA |
| **437** | KRM | KRUENG MANE | KERETA |
| **438** | KRI | KRANJI | KERETA |
| **439** | KRG | KRUENG GEUKUEH | KERETA |
| **440** | RW | RAWA BUAYA | KERETA |
| **441** | KRA | KARANGANTU | KERETA |
| **442** | S-01 PGD | LRTJ \- PEGANGSAAN DUA, KELAPA GADING | KERETA |
| **443** | KPT | KERTAPATI | KERETA |
| **444** | KPS | KAPAS | KERETA |
| **445** | S-02 BVU | LRTJ \- BOULEVARD UTARA | KERETA |
| **446** | KPN | KEPANJEN | KERETA |
| **447** | KPB | KAMPUNG BANDAN | KERETA |
| **448** | KOS | KOSAMBI | KERETA |
| **449** | S-03 BVS | LRTJ \- BOULEVARD SELATAN | KERETA |
| **450** | PAK | PAUH KAMBAR | KERETA |
| **451** | RBP | RAMBIPUJI | KERETA |
| **452** | PBA | PERBAUNGAN | KERETA |
| **453** | RCK | RANCAEKEK | KERETA |
| **454** | PB | PROBOLINGGO | KERETA |
| **455** | PDRG | PONDOK RAJEG | KERETA |
| **456** | RGP | ROGOJAMPI | KERETA |
| **457** | LDT | LIDAHTANAH | KERETA |
| **458** | LBY | LUBUK BUAYA | KERETA |
| **459** | PAS | PASIRBUNGUR | KERETA |
| **460** | LBT | LUBUK BATANG | KERETA |
| **461** | LBP | LUBUKPAKAM | KERETA |
| **462** | PAN | PANCORAN | KERETA |
| **463** | MP | MARTAPURA | KERETA |
| **464** | MR | MOJOKERTO | KERETA |
| **465** | RH | RENDEH | KERETA |
| **466** | S-04 PUM | LRTJ \- PULOMAS | KERETA |
| **467** | LAR | LABUAN RATU | KERETA |
| **468** | RJ | REJOTANGAN | KERETA |
| **469** | MRG | MA'RANG | KERETA |
| **470** | LAG | LALANG | KERETA |
| **471** | LA | LUBUK ALUNG | KERETA |
| **472** | MRI | MANGGARAI | KERETA |
| **473** | PAB | PABUARAN | KERETA |
| **474** | MRL | MUARAGULA | KERETA |
| **475** | MRS | MAROS | KERETA |
| **476** | RJS | REJOSARI | KERETA |
| **477** | KYA | KROYA | KERETA |
| **478** | RJW | RAJAWALI | KERETA |
| **479** | MSG | MASENG | KERETA |
| **480** | KWN | KUTOWINANGUN | KERETA |
| **481** | RK | RANGKASBITUNG | KERETA |
| **482** | BIJ | BINJAI | KERETA |
| **483** | BDR | BANDARA | KERETA |
| **484** | BDT | BANDARTINGGI | KERETA |
| **485** | BEK | BEKASI BARAT | KERETA |
| **486** | BG | BANGIL | KERETA |
| **487** | TBI | TEBING TINGGI | KERETA |
| **488** | BGM | BUNGAMAS | KERETA |
| **489** | BH | BOHARAN | KERETA |
| **490** | BIB | BLIMBING | KERETA |
| **491** | TB | TAMBUN | KERETA |
| **492** | BD | BANDUNG | KERETA |
| **493** | TAN | TANETE RILAU | KERETA |
| **494** | TAL | TALUN | KERETA |
| **495** | BIM | BANDARA INTERNASIONAL MINANGKABAU | KERETA |
| **496** | TAB | TABING | KERETA |
| **497** | BJ | BOJONEGORO | KERETA |
| **498** | BJD | BOJONGGEDE | KERETA |
| **499** | BJL | BAJALINGGEI | KERETA |
| **500** | BJR | BANJAR | KERETA |
| **501** | BAP | BANDARHALIPAH | KERETA |
| **502** | TGL | KCJB \- TEGALLUAR | KERETA |
| **503** | TGI | TEGINENENG | KERETA |
| **504** | TGD | TANJUNG GADING | KERETA |
| **505** | TGA | TANGGULANGIN | KERETA |
| **506** | TG | TEGAL | KERETA |
| **507** | TES | TANDES | KERETA |
| **508** | TEJ | TENJO | KERETA |
| **509** | TEB | TEBET | KERETA |
| **510** | TA | TULUNGAGUNG | KERETA |
| **511** | TDM | TARANDAM | KERETA |
| **512** | BAR | BARRU | KERETA |
| **513** | BB | BREBES | KERETA |
| **514** | BBA | BLAMBANGAN PAGAR | KERETA |
| **515** | BBG | BRUMBUNG | KERETA |
| **516** | BBN | BRAMBANAN | KERETA |
| **517** | BBT | BABAT | KERETA |
| **518** | BBU | BLAMBANGAN UMPU | KERETA |
| **519** | SR | SRAGEN | KERETA |
| **520** | BPR | BATUCEPER | KERETA |
| **521** | PPK | PRUPUK | KERETA |
| **522** | STA | SOLOKOTA | KERETA |
| **523** | SRP | SERPONG | KERETA |
| **524** | SRJ | SUMBERREJO | KERETA |
| **525** | BST | BANDARA SOEKARNO-HATTA | KERETA |
| **526** | BTA | BATURAJA | KERETA |
| **527** | BTG | BATANG | KERETA |
| **528** | SUD | SUDIRMAN | KERETA |
| **529** | BTK | BATANGKUIS | KERETA |
| **530** | BTT | BATUTULIS | KERETA |
| **531** | BUA | BUARAN | KERETA |
| **532** | BUS | BUMI SRIWIJAYA | KERETA |
| **533** | SPJ | SEPANJANG | KERETA |
| **534** | BWI | BANYUWANGI KOTA | KERETA |
| **535** | SPH | SUMPIUH | KERETA |
| **536** | BWO | BOWERNO | KERETA |
| **537** | BMA | BUMIAYU | KERETA |
| **538** | BKH | BUNGKAIH | KERETA |
| **539** | BKI | BEKRI | KERETA |
| **540** | BKN | BABAKAN | KERETA |
| **541** | BKS | BEKASI | KERETA |
| **542** | SWT | SROWOT | KERETA |
| **543** | BKST | BEKASI TIMUR | KERETA |
| **544** | BL | BLITAR | KERETA |
| **545** | SWL | SAWAHLUNTO | KERETA |
| **546** | TGL | TANGGUL | KERETA |
| **547** | BMB | BAMBAN | KERETA |
| **548** | SW | SAWAH BESAR | KERETA |
| **549** | SUT | SUKATANI | KERETA |
| **550** | BNW | BENOWO | KERETA |
| **551** | SUDB | SUDIRMAN BARU | KERETA |
| **552** | BOI | BOJONG INDAH | KERETA |
| **553** | BOO | BOGOR | KERETA |
| **554** | BOP | BOGOR PALEDANG | KERETA |
| **555** | WG | WLINGI | KERETA |
| **556** | WNR | WANARAJA | KERETA |
| **557** | WNG | WONOGIRI | KERETA |
| **558** | WLT | WALANTAKA | KERETA |
| **559** | WLR | WELERI | KERETA |
| **560** | WK | WALIKUKUN | KERETA |
| **561** | WJ | WOJO | KERETA |
| **562** | PLM | PALMERAH | KERETA |
| **563** | PME | PAMINGKE | KERETA |
| **564** | AWN | ARJAWINANGUN | KERETA |
| **565** | PKJ | PANGKAJENE | KERETA |
| **566** | WDU | WADU | KERETA |
| **567** | PK | PEKALONGAN | KERETA |
| **568** | WAY | WAYTUBA | KERETA |
| **569** | PML | PEMALANG | KERETA |
| **570** | UP | UNIVERSITAS PANCASILA | KERETA |
| **571** | UJM | UJANMAS | KERETA |
| **572** | UI | UNIVERSITAS INDONESIA | KERETA |
| **573** | PLA | PULAU AIE | KERETA |
| **574** | PLD | PLERED | KERETA |
| **575** | ABR | AMBARAWA | KERETA |
| **576** | AC | ANCOL | KERETA |
| **577** | AI | PASAR ALAI | KERETA |
| **578** | YK | YOGYAKARTA | KERETA |
| **579** | YIA | YOGYAKARTA INTERNATIONAL AIRPORT | KERETA |
| **580** | AK | ANGKE | KERETA |
| **581** | AMP | AMPERA | KERETA |
| **582** | PMN | PARIAMAN | KERETA |
| **583** | WT | WATES | KERETA |
| **584** | PL | PALUR | KERETA |
| **585** | ARB | ARASKABU | KERETA |
| **586** | ASH | ASRAMA HAJI | KERETA |
| **587** | AT | AIR TAWAR | KERETA |
| **588** | ATA | ALASTUA | KERETA |
| **589** | WR | WARU | KERETA |
| **590** | WO | WONOKROMO | KERETA |
| **591** | TKE | TELUK MENGKUDU | KERETA |
| **592** | POC | PONDOK CINA | KERETA |
| **593** | TLY | TULUNG BUYUT | KERETA |
| **594** | TLW | TELAWA | KERETA |
| **595** | TLP | TALANG PADANG | KERETA |
| **596** | TLN | TULANGAN | KERETA |
| **597** | POK | PONDOKJATI | KERETA |
| **598** | TKO | TAMAN KOTA | KERETA |
| **599** | POL | POLRESTA | KERETA |
| **600** | TMI | TMII | KERETA |
| **601** | TJS | TANJUNGRASA | KERETA |
| **602** | TIS | TERISI | KERETA |
| **603** | TI | TEBINGTINGGI | KERETA |
| **604** | THN | TARAHAN | KERETA |
| **605** | THB | TANAHABANG | KERETA |
| **606** | TGS | TIGARAKSA | KERETA |
| **607** | TGR | TEMUGURUH | KERETA |
| **608** | TGP | TANJUNG | KERETA |
| **609** | PNM | PENIMUR | KERETA |
| **610** | PI | PORIS | KERETA |
| **611** | PHA | PADANGHALABAN | KERETA |
| **612** | PNG | PENANGGIRAN | KERETA |
| **613** | TTI | TANAH TINGGI | KERETA |
| **614** | TSM | TASIKMALAYA | KERETA |
| **615** | PGJ | POGAJIH | KERETA |
| **616** | TRK | TARIK | KERETA |
| **617** | PGB | PEGADENBARU | KERETA |
| **618** | IDO | INDRO | KERETA |
| **619** | TPK | TANJUNGPRIUK | KERETA |
| **620** | TOJB | TONJONG BARU | KERETA |
| **621** | TNT | TANJUNG BARAT | KERETA |
| **622** | TNK | TANJUNG KARANG | KERETA |
| **623** | PNT | PASARNGUTER | KERETA |
| **624** | TNG | TANGERANG | KERETA |
| **625** | TNB | TANJUNGBALAI | KERETA |
| **626** | PNW | PENINJAWAN | KERETA |
| **627** | CRJ | CIRANJANG | KERETA |
| **628** | GDD | GONDANGDIA | KERETA |
| **629** | CUK | CAKUNG | KERETA |
| **630** | CU | CEPU | KERETA |
| **631** | GDG | GEDANGAN | KERETA |
| **632** | CTR | CITERAS | KERETA |
| **633** | CTH | CIKUDAPATEUH | KERETA |
| **634** | CTA | CITAYAM | KERETA |
| **635** | CT | CATANG | KERETA |
| **636** | GDM | GANDRUNGMANGUN | KERETA |
| **637** | GDS | GANDASOLI | KERETA |
| **638** | CSK | CISAUK | KERETA |
| **639** | GHM | GIHAM | KERETA |
| **640** | CSA | CISAAT | KERETA |
| **641** | GK | GADOBANGKONG | KERETA |
| **642** | CRM | CURAHMALANG | KERETA |
| **643** | GDB | GEDEBAGE | KERETA |
| **644** | CRG | CIREUNGAS | KERETA |
| **645** | CRC | CIRACAS | KERETA |
| **646** | GM | GUMILIR | KERETA |
| **647** | CRB | CARUBAN | KERETA |
| **648** | CPY | CIPEUYEUM | KERETA |
| **649** | SMT | SEMARANG TAWANG BANK JATENG | KERETA |
| **650** | CPT | CIPATAT | KERETA |
| **651** | CPH | CIMPARUH | KERETA |
| **652** | CPD | CIPEUNDEUY | KERETA |
| **653** | CP | CILACAP | KERETA |
| **654** | CNP | CIREBON PRUJAKAN | KERETA |
| **655** | CND | CINDE | KERETA |
| **656** | CN | CIREBON | KERETA |
| **657** | CMK | CIMEKAR | KERETA |
| **658** | GB | GOMBONG | KERETA |
| **659** | SLM | SALEM | KERETA |
| **660** | DWN | DAWUAN | KERETA |
| **661** | SLO | SOLO BALAPAN | KERETA |
| **662** | DUK | DUKU | KERETA |
| **663** | DU | DURI | KERETA |
| **664** | SLS | SULUSUBAN | KERETA |
| **665** | SKT | SASAKSAAT | KERETA |
| **666** | DRN | DUREN KALIBATA | KERETA |
| **667** | SLW | SLAWI | KERETA |
| **668** | GAD | GARUDA DEMPO | KERETA |
| **669** | SMB | SEMBUNG | KERETA |
| **670** | DPL | DOPLANG | KERETA |
| **671** | DPB | DEPOK BARU | KERETA |
| **672** | GAR | GARONGKONG | KERETA |
| **673** | GMR | GAMBIR | KERETA |
| **674** | DP | DEPOK | KERETA |
| **675** | SMC | SEMARANG PONCOL | KERETA |
| **676** | GBN | GAMBRINGAN | KERETA |
| **677** | DMR | DOLOKMERANGIR | KERETA |
| **678** | DMG | DEMANG | KERETA |
| **679** | DL | DELANGGU | KERETA |
| **680** | DKA | DUKUH ATAS | KERETA |
| **681** | DJKA | DJKA | KERETA |
| **682** | SMO | ADI SOEMARMO | KERETA |
| **683** | DIS | DISHUB | KERETA |
| **684** | DD | DUDUK | KERETA |
| **685** | DAR | DARU | KERETA |
| **686** | GD | GUNDIH | KERETA |
| **687** | CW | CAWANG | KERETA |
| **688** | HAR | HARJAMUKTI | KERETA |
| **689** | CJT | CILEJIT | KERETA |
| **690** | CJ | CIANJUR | KERETA |
| **691** | CIT | CIBITUNG | KERETA |
| **692** | CIR | CIROYOM | KERETA |
| **693** | CIL | CILIWUNG | KERETA |
| **694** | GST | GANG SENTIONG | KERETA |
| **695** | CID | CILEDUG | KERETA |
| **696** | CMI | CIMAHI | KERETA |
| **697** | GUB | GUBUG | KERETA |
| **698** | CGB | CIGOMBONG | KERETA |
| **699** | CEL | CICALENGKA | KERETA |
| **700** | GW | GAWOK | KERETA |
| **701** | CE | CEPER | KERETA |
| **702** | SIR | SIANTAR | KERETA |
| **703** | CI | CIAMIS | KERETA |
| **704** | HGL | HAURGEULIS | KERETA |
| **705** | CD | CIKADONGDONG | KERETA |
| **706** | CCR | CICURUG | KERETA |
| **707** | HJP | HAJI PEMANGGILAN | KERETA |
| **708** | CC | CICAYUR | KERETA |
| **709** | HLM | HALIM | KERETA |
| **710** | CBR | CIBUNGUR | KERETA |
| **711** | HRP | HAURPUGUR | KERETA |
| **712** | CBN | CIBINONG | KERETA |
| **713** | CBD | CIBADAK | KERETA |
| **714** | CBB | CIBEBER | KERETA |
| **715** | CB | CIBATU | KERETA |
| **716** | SI | SUKABUMI | KERETA |
| **717** | CA | CIGANEA | KERETA |
| **718** | CK1 | CIKUNIR I | KERETA |
| **719** | CME | CERME | KERETA |
| **720** | CMD | CIMINDI | KERETA |
| **721** | CLT | CILEBUT | KERETA |
| **722** | SKH | SUKOHARJO | KERETA |
| **723** | CLG | CILEGON | KERETA |
| **724** | CLE | CILAME | KERETA |
| **725** | SK | SOLOJEBRES | KERETA |
| **726** | CKY | CIKOYA | KERETA |
| **727** | GRG | GROGOL | KERETA |
| **728** | CKR | CIKARANG | KERETA |
| **729** | GRK | GEURUGOK | KERETA |
| **730** | CKP | CIKAMPEK | KERETA |
| **731** | CKI | CIKINI | KERETA |
| **732** | GRM | GARUM | KERETA |
| **733** | GRT | GARUT | KERETA |
| **734** | CKL | CIKEUSAL | KERETA |
| **735** | CK2 | CIKUNIR II | KERETA |
| **736** | CKK | CIKOKO | KERETA |
| **737** | PP-PMA | Pam | Pelabuhan |
| **738** | SGQ | Sangatta | Pelabuhan |
| **739** | PP-MTK | Muntok (Tanjung Kalian) | Pelabuhan |
| **740** | PNN | Pananaru | Pelabuhan |
| **741** | PP-MPT | Marapokot | Pelabuhan |
| **742** | PP-SWA | Siwa | Pelabuhan |
| **743** | PNN | Patimban | Pelabuhan |
| **744** | PP-SPZ | Saparua | Pelabuhan |
| **745** | PP-PMM | Pemana | Pelabuhan |
| **746** | PP-MAW | Mawasangka | Pelabuhan |
| **747** | PP-POK | Poka | Pelabuhan |
| **748** | PP-PMA | Pamatata | Pelabuhan |
| **749** | PP-PTT | Pattumbukan | Pelabuhan |
| **750** | PP-PRE | Pure | Pelabuhan |
| **751** | PP-SUM | Saumlaki | Pelabuhan |
| **752** | PP-PNJ | Penajam | Pelabuhan |
| **753** | PP-MNM | Tanjung Nyato | Pelabuhan |
| **754** | PNK | Pontianak | Pelabuhan |
| **755** | PP-NML | Namlea | Pelabuhan |
| **756** | PP-MEQ | Meulaboh | Pelabuhan |
| **757** | PP-PTA | Poto Tano | Pelabuhan |
| **758** | PP-PLT | Pulau Laut | Pelabuhan |
| **759** | PP-PTE | Pulau Tello | Pelabuhan |
| **760** | PP-STG | Setagen | Pelabuhan |
| **761** | PP-STT | Sintete | Pelabuhan |
| **762** | PMK | Pomako | Pelabuhan |
| **763** | PP-MMK | Matak | Pelabuhan |
| **764** | PNJ | Panjang | Pelabuhan |
| **765** | PP-PNK | Penarik | Pelabuhan |
| **766** | PP-KDR | Kendari | Pelabuhan |
| **767** | PP-RNR | Ndao | Pelabuhan |
| **768** | PP-DOM | Dompak | Pelabuhan |
| **769** | PP-GLL | Galala | Pelabuhan |
| **770** | PP-LKO | Lakor | Pelabuhan |
| **771** | PP-LHJ | Labuhan Haji | Pelabuhan |
| **772** | PP-GRK | Garongkong | Pelabuhan |
| **773** | PP-GRO | Gorom | Pelabuhan |
| **774** | PP-LGG | Langara | Pelabuhan |
| **775** | PP-LBR | Lembar | Pelabuhan |
| **776** | PP-HNM | Hunimua | Pelabuhan |
| **777** | PP-HRM | Haruku | Pelabuhan |
| **778** | PP-ILW | Ilwaki | Pelabuhan |
| **779** | PP-RNR | Rote | Pelabuhan |
| **780** | PP-JPR | Jepara | Pelabuhan |
| **781** | PP-SDI | Sadai | Pelabuhan |
| **782** | PP-DOB | Dobo | Pelabuhan |
| **783** | PP-KGL | Kuala Tungkal | Pelabuhan |
| **784** | PP-LBJ | Labuan Bajo | Pelabuhan |
| **785** | PP-KYG | Kayangan | Pelabuhan |
| **786** | PP-KKA | Kadatua | Pelabuhan |
| **787** | PP-KLB | Kalabahi | Pelabuhan |
| **788** | PP-SBU | Sebuku | Pelabuhan |
| **789** | PP-KLD | Kaledupa | Pelabuhan |
| **790** | PP-KLK | Kolaka | Pelabuhan |
| **791** | PP-KMA | Kamaru | Pelabuhan |
| **792** | PP-KRU | Kariangau Balikpapan | Pelabuhan |
| **793** | PPS | Pulang Pisau | Pelabuhan |
| **794** | PP-KSA | Kisar | Pelabuhan |
| **795** | PP-KSU | Kesui | Pelabuhan |
| **796** | PP-KWA | Kewapante | Pelabuhan |
| **797** | PP-BBU | Bau Bau | Pelabuhan |
| **798** | PP-LUB | Labuan | Pelabuhan |
| **799** | PP-SPE | Sape | Pelabuhan |
| **800** | PP-LTW | Letwurung | Pelabuhan |
| **801** | POJ | Palopo | Pelabuhan |
| **802** | PP-LSS | Tobaku | Pelabuhan |
| **803** | PP-SNG | Sinabang | Pelabuhan |
| **804** | PP-AAH | Amahai | Pelabuhan |
| **805** | PP-SLO | Pulau Solor/Solor | Pelabuhan |
| **806** | PP-SEZ | Seba | Pelabuhan |
| **807** | PP-ALE | Amolengo | Pelabuhan |
| **808** | PP-AMR | Aimere | Pelabuhan |
| **809** | PP-AON | Adonara | Pelabuhan |
| **810** | PP-LRT | Larantuka | Pelabuhan |
| **811** | PP-ARX | Airnanang | Pelabuhan |
| **812** | PP-LWL | Lewoleba | Pelabuhan |
| **813** | PP-BJO | Bajoe | Pelabuhan |
| **814** | PP-LRA | Larat | Pelabuhan |
| **815** | PP-LMK | Lamerang | Pelabuhan |
| **816** | PP-BKL | Bakalang | Pelabuhan |
| **817** | PP-BLH | Balohan | Pelabuhan |
| **818** | PP-BLN | Batulicin | Pelabuhan |
| **819** | PP-SEA | Selat Belia | Pelabuhan |
| **820** | PP-BNB | Bombana | Pelabuhan |
| **821** | PP-BNJ | Benjina | Pelabuhan |
| **822** | PP-BOO | Bolok Kupang | Pelabuhan |
| **823** | PP-BRC | Baranusa | Pelabuhan |
| **824** | PP-BRR | Bira | Pelabuhan |
| **825** | PP-RHA | Raha | Pelabuhan |
| **826** | PP-DGK | Dongkala | Pelabuhan |
| **827** | TDA | Teluk Dalam | Pelabuhan |
| **828** | TAA | Tilamuta | Pelabuhan |
| **829** | TAG | Kota Agung | Pelabuhan |
| **830** | TBB | Teluk Batang | Pelabuhan |
| **831** | TBE | Tanjung Beringin | Pelabuhan |
| **832** | TBO | Tobelo | Pelabuhan |
| **833** | TBO | Tobelo | Pelabuhan |
| **834** | TBS | Teluk Bungus | Pelabuhan |
| **835** | TBY | Teluk Bayur | Pelabuhan |
| **836** | SYG | Senayang | Pelabuhan |
| **837** | TEG | Tegal | Pelabuhan |
| **838** | TEI | Ternate | Pelabuhan |
| **839** | TEK | Batahan | Pelabuhan |
| **840** | TGR | Tigaras | Pelabuhan |
| **841** | TGU | Telaga Biru | Pelabuhan |
| **842** | THA | Tahuna | Pelabuhan |
| **843** | THR | Tana Paser | Pelabuhan |
| **844** | TIP | Taipa | Pelabuhan |
| **845** | SRI | Samarinda | Pelabuhan |
| **846** | SON | Siuban | Pelabuhan |
| **847** | SOQ | Sorong | Pelabuhan |
| **848** | SPG | Sipinggan | Pelabuhan |
| **849** | SPI | Sapudi | Pelabuhan |
| **850** | SPN | Sapeken | Pelabuhan |
| **851** | SQN | Sanana | Pelabuhan |
| **852** | SQQ | Tanjung Selor | Pelabuhan |
| **853** | SRG | Tanjung Emas | Pelabuhan |
| **854** | TJB | Tanjung Balai Karimun | Pelabuhan |
| **855** | SRU | Sirombu | Pelabuhan |
| **856** | SST | Muara Siberut | Pelabuhan |
| **857** | STU | Satui | Pelabuhan |
| **858** | SUB | Tanjung Perak | Pelabuhan |
| **859** | SUQ | Sungai Guntung | Pelabuhan |
| **860** | SUS | Susoh | Pelabuhan |
| **861** | SWA | Siwa | Pelabuhan |
| **862** | SXK | Saumlaki | Pelabuhan |
| **863** | WGP | Waingapu | Pelabuhan |
| **864** | TUA | Tual | Pelabuhan |
| **865** | TUH | Tulehu | Pelabuhan |
| **866** | TXM | Teminabuan | Pelabuhan |
| **867** | USI | Ulu Siau | Pelabuhan |
| **868** | WCI | Wanci | Pelabuhan |
| **869** | WDA | Weda | Pelabuhan |
| **870** | WED | Weda | Pelabuhan |
| **871** | WGO | Waigeo (Raja Ampat) | Pelabuhan |
| **872** | TTO | Toli-Toli | Pelabuhan |
| **873** | WII | Wini | Pelabuhan |
| **874** | WIO | Waikelo | Pelabuhan |
| **875** | WKI | Wakai | Pelabuhan |
| **876** | WRN | Waren | Pelabuhan |
| **877** | WSR | Wasior | Pelabuhan |
| **878** | YAR | Selayar | Pelabuhan |
| **879** | ZRI | Serui | Pelabuhan |
| **880** | ZRM | Sarmi | Pelabuhan |
| **881** | TPJ | Tuapeijat | Pelabuhan |
| **882** | TJQ | Tanjung Pandan | Pelabuhan |
| **883** | TKG | Bakauheni | Pelabuhan |
| **884** | TLI | Toli-Toli | Pelabuhan |
| **885** | TLN | Tembilahan | Pelabuhan |
| **886** | TMD | Tanjung Medang | Pelabuhan |
| **887** | TMP | Tarempa | Pelabuhan |
| **888** | TNJ | Tanjung Pinang | Pelabuhan |
| **889** | TNU | Talang Duku | Pelabuhan |
| **890** | SNY | Sungai Nyamuk | Pelabuhan |
| **891** | TPK | Tapaktuan | Pelabuhan |
| **892** | TPS | Tanjung Pakis | Pelabuhan |
| **893** | TQP | Kupang | Pelabuhan |
| **894** | TRE | Tanjung Redep | Pelabuhan |
| **895** | TSL | Tanjung Sarang Elang | Pelabuhan |
| **896** | TSX | Tanjung Santan | Pelabuhan |
| **897** | TTK | Nunukan | Pelabuhan |
| **898** | TTN | Teluk Sigintung | Pelabuhan |
| **899** | PTC | Patani | Pelabuhan |
| **900** | PP-WRT | Waipirit | Pelabuhan |
| **901** | PQR | Pekanbaru | Pelabuhan |
| **902** | PRA | Pelabuhan Ratu | Pelabuhan |
| **903** | PRI | Parigi | Pelabuhan |
| **904** | PRN | Panarukan | Pelabuhan |
| **905** | PRO | Probolinggo/Tanjung Tembaga | Pelabuhan |
| **906** | PSJ | Poso | Pelabuhan |
| **907** | PTA | Padang Tikar | Pelabuhan |
| **908** | PP-WNC | Wanci | Pelabuhan |
| **909** | PTE | Pulau Tello | Pelabuhan |
| **910** | PTL | Teluk Palu | Pelabuhan |
| **911** | PUM | Pomalaa | Pelabuhan |
| **912** | RAQ | Raha | Pelabuhan |
| **913** | REO | Reo | Pelabuhan |
| **914** | RGG | Rangga Ilung | Pelabuhan |
| **915** | RGT | Kuala Cinaku | Pelabuhan |
| **916** | RNE | Rembang | Pelabuhan |
| **917** | PP-TMP | Tampo | Pelabuhan |
| **918** | PP-TDL | Teluk Dalam | Pelabuhan |
| **919** | PP-TDS | Tondasi | Pelabuhan |
| **920** | PP-TGP | Tanjung Api-Api | Pelabuhan |
| **921** | PP-TJR | Tanjung RU | Pelabuhan |
| **922** | PP-TLA | Pulau Tayando/Toyando Yamtel | Pelabuhan |
| **923** | PP-TLO | Tolandona | Pelabuhan |
| **924** | PP-TMB | Tambelan | Pelabuhan |
| **925** | PP-TMI | Tomia | Pelabuhan |
| **926** | RUM | Rum | Pelabuhan |
| **927** | PP-TPR | Telaga Punggur | Pelabuhan |
| **928** | PP-TRB | Torobulu | Pelabuhan |
| **929** | PP-TRJ | Tarjun | Pelabuhan |
| **930** | PP-TSG | Tanjung Serdang | Pelabuhan |
| **931** | PP-TUB | Tanjung Uban | Pelabuhan |
| **932** | PP-WAP | Waingapu | Pelabuhan |
| **933** | PP-WAR | Waara | Pelabuhan |
| **934** | PP-WKL | Waikelo | Pelabuhan |
| **935** | SLW | Salawati | Pelabuhan |
| **936** | SKE | Sunda Kelapa | Pelabuhan |
| **937** | SKI | Sangkulirang | Pelabuhan |
| **938** | SKJ | Sei Kolak Kijang | Pelabuhan |
| **939** | SKK | Sikakap | Pelabuhan |
| **940** | SLG | Sibolga | Pelabuhan |
| **941** | SLJ | Selat Panjang | Pelabuhan |
| **942** | SLN | Salakan (Saiyong) | Pelabuhan |
| **943** | SLU | Sungai Lumpur | Pelabuhan |
| **944** | SKE | Saketa | Pelabuhan |
| **945** | SMD | Simanindo | Pelabuhan |
| **946** | SMP | Nusa Penida/Sampalan | Pelabuhan |
| **947** | SMQ | Sampit | Pelabuhan |
| **948** | SNE | Sintete | Pelabuhan |
| **949** | SNG | Sinabang | Pelabuhan |
| **950** | SNL | Singkil | Pelabuhan |
| **951** | SNP | Branta | Pelabuhan |
| **952** | SNV | Sanana | Pelabuhan |
| **953** | SEQ | Tanjung Buton | Pelabuhan |
| **954** | SAA | Sukamara | Pelabuhan |
| **955** | SAP | Sape | Pelabuhan |
| **956** | SBA | Subaim | Pelabuhan |
| **957** | SBG | Sabang | Pelabuhan |
| **958** | SBR | Siberut | Pelabuhan |
| **959** | SDC | Sidangole | Pelabuhan |
| **960** | SDI | Sadai | Pelabuhan |
| **961** | SEA | Seba | Pelabuhan |
| **962** | PP-TAL | Tual | Pelabuhan |
| **963** | SFF | Sofifi | Pelabuhan |
| **964** | SFI | Sofifi | Pelabuhan |
| **965** | MNB | Teluk Batang | Pelabuhan |
| **966** | SIK | Sikakap | Pelabuhan |
| **967** | SIO | Soasio | Pelabuhan |
| **968** | SJI | Sinjai | Pelabuhan |
| **969** | SKA | Saketa | Pelabuhan |
| **970** | KSA | Kuala Samboja | Pelabuhan |
| **971** | KNG | Kaimana | Pelabuhan |
| **972** | KNL | Kolonedale | Pelabuhan |
| **973** | KNP | Kintap | Pelabuhan |
| **974** | KNU | Karangantu | Pelabuhan |
| **975** | KOK | Kokas | Pelabuhan |
| **976** | KOL | Kolaka | Pelabuhan |
| **977** | KRB | Kepulauan Seribu | Pelabuhan |
| **978** | KRO | Korido | Pelabuhan |
| **979** | KME | Kuala Mendahara | Pelabuhan |
| **980** | KTB | Kotabaru Batulicin | Pelabuhan |
| **981** | KTG | Ketapang | Pelabuhan |
| **982** | KTJ | Kuala Tanjung | Pelabuhan |
| **983** | KTK | Kuala Tungkal | Pelabuhan |
| **984** | KTP | Ketapang | Pelabuhan |
| **985** | KUA | Kuala Langsa | Pelabuhan |
| **986** | KUM | Kumai | Pelabuhan |
| **987** | KWG | Kwandang | Pelabuhan |
| **988** | KAT | Kalianget | Pelabuhan |
| **989** | IRU | Indramayu | Pelabuhan |
| **990** | ITN | Tanjung Uban | Pelabuhan |
| **991** | JEO | Jeneponto | Pelabuhan |
| **992** | JEP | Jepara | Pelabuhan |
| **993** | JIO | Jailolo | Pelabuhan |
| **994** | JKT | Tanjung Priok | Pelabuhan |
| **995** | JWA | Juwana | Pelabuhan |
| **996** | KAN | Kotabunan | Pelabuhan |
| **997** | KXR | Wonreli | Pelabuhan |
| **998** | KAW | Kawaluso | Pelabuhan |
| **999** | KBH | Kalabahi | Pelabuhan |
| **1000** | KDI | Kendari | Pelabuhan |
| **1001** | KDO | Klademak | Pelabuhan |
| **1002** | KDW | Kendawangan | Pelabuhan |
| **1003** | KGQ | Kuala Gaung | Pelabuhan |
| **1004** | KJA | Karimun Jawa | Pelabuhan |
| **1005** | MGA | Manggala/Menggala | Pelabuhan |
| **1006** | LWE | Lewoleba | Pelabuhan |
| **1007** | LWI | Laiwui | Pelabuhan |
| **1008** | LWK | Luwuk | Pelabuhan |
| **1009** | MAJ | Majene | Pelabuhan |
| **1010** | MAK | Makassar | Pelabuhan |
| **1011** | MDC | Manado | Pelabuhan |
| **1012** | MEN | Pemenang/Tanjung | Pelabuhan |
| **1013** | MEQ | Meulaboh | Pelabuhan |
| **1014** | LUW | Luwuk | Pelabuhan |
| **1015** | MGE | Melonguane | Pelabuhan |
| **1016** | MII | Maccini Baji | Pelabuhan |
| **1017** | MJU | Mamuju | Pelabuhan |
| **1018** | MKA | Muara Angke | Pelabuhan |
| **1019** | MKI | Malakoni-Enggano | Pelabuhan |
| **1020** | MKQ | Merauke | Pelabuhan |
| **1021** | MKT | Likupang | Pelabuhan |
| **1022** | LIG | Teluk Leidong | Pelabuhan |
| **1023** | KYO | Kayoa | Pelabuhan |
| **1024** | KYU | Kahyapu | Pelabuhan |
| **1025** | LAJ | Labuhan | Pelabuhan |
| **1026** | LBH | Babang | Pelabuhan |
| **1027** | LBI | Bintuhan | Pelabuhan |
| **1028** | LBO | Labuan Bajo | Pelabuhan |
| **1029** | LEK | Leok | Pelabuhan |
| **1030** | LHA | Lahewa | Pelabuhan |
| **1031** | HUU | Hatu Piru | Pelabuhan |
| **1032** | LKA | Larantuka | Pelabuhan |
| **1033** | LLO | Labuhan Lombok | Pelabuhan |
| **1034** | LMA | Labuhan Maringgai | Pelabuhan |
| **1035** | LMR | Lembar | Pelabuhan |
| **1036** | LPO | Lapuko | Pelabuhan |
| **1037** | LSW | Lhokseumawe | Pelabuhan |
| **1038** | LUK | Labuhan Uki | Pelabuhan |
| **1039** | BOA | Benoa | Pelabuhan |
| **1040** | BKI | Bengkalis | Pelabuhan |
| **1041** | BKS | Bengkulu | Pelabuhan |
| **1042** | BLT | Manggar | Pelabuhan |
| **1043** | BLW | Belawan | Pelabuhan |
| **1044** | BLY | Buli | Pelabuhan |
| **1045** | BMJ | Bias Munjul | Pelabuhan |
| **1046** | BMU | Bima | Pelabuhan |
| **1047** | BNU | Bungku | Pelabuhan |
| **1048** | BJU | Tanjung Wangi | Pelabuhan |
| **1049** | BPN | Balikpapan | Pelabuhan |
| **1050** | BRE | Baturube | Pelabuhan |
| **1051** | BRO | Baa | Pelabuhan |
| **1052** | BRS | Barus | Pelabuhan |
| **1053** | BRU | Palembang | Pelabuhan |
| **1054** | BSG | Bastiong \- Ternate | Pelabuhan |
| **1055** | BSH | Tanjung Balai Asahan | Pelabuhan |
| **1056** | BTG | Batang | Pelabuhan |
| **1057** | BEN | Benete | Pelabuhan |
| **1058** | BAA | Baranusa | Pelabuhan |
| **1059** | BAD | Badas | Pelabuhan |
| **1060** | BAE | Bajoe | Pelabuhan |
| **1061** | BBA | Babang | Pelabuhan |
| **1062** | BBE | Bulukumba | Pelabuhan |
| **1063** | BBG | Bobong | Pelabuhan |
| **1064** | BBM | Belang-Belang | Pelabuhan |
| **1065** | BDJ | Banjarmasin | Pelabuhan |
| **1066** | BTN | Banten | Pelabuhan |
| **1067** | BGG | Banggai | Pelabuhan |
| **1068** | BGI | Banggai | Pelabuhan |
| **1069** | BII | Bagan Siapi-Api | Pelabuhan |
| **1070** | BIK | Biak | Pelabuhan |
| **1071** | BIK | Mokmer | Pelabuhan |
| **1072** | BIO | Anggrek | Pelabuhan |
| **1073** | BIT | Bitung | Pelabuhan |
| **1074** | FLY | Folley | Pelabuhan |
| **1075** | DOW | Dowora | Pelabuhan |
| **1076** | DRA | Daruba | Pelabuhan |
| **1077** | DRB | Daruba | Pelabuhan |
| **1078** | DTA | Batang Dua | Pelabuhan |
| **1079** | DUM | Dumai | Pelabuhan |
| **1080** | ENE | Ende | Pelabuhan |
| **1081** | ENO | Kuala Enok | Pelabuhan |
| **1082** | FAI | Fak-Fak | Pelabuhan |
| **1083** | DOB | Dobo | Pelabuhan |
| **1084** | GBE | Gebe | Pelabuhan |
| **1085** | GER | Geser | Pelabuhan |
| **1086** | GMK | Gilimanuk | Pelabuhan |
| **1087** | GNG | Garongkong | Pelabuhan |
| **1088** | GNS | Gunung Sitoli | Pelabuhan |
| **1089** | GRE | Gresik | Pelabuhan |
| **1090** | GTO | Gorontalo | Pelabuhan |
| **1091** | BYQ | Pulau Bunyu | Pelabuhan |
| **1092** | BTT | Batanta | Pelabuhan |
| **1093** | BUA | Bula | Pelabuhan |
| **1094** | BUD | Bunta | Pelabuhan |
| **1095** | BUR | Batam | Pelabuhan |
| **1096** | BUW | Baubau | Pelabuhan |
| **1097** | BWN | Bawean | Pelabuhan |
| **1098** | BXD | Bade | Pelabuhan |
| **1099** | BXT | Bontang | Pelabuhan |
| **1100** | PLB | Pangkalan Bun | Pelabuhan |
| **1101** | CBN | Cirebon | Pelabuhan |
| **1102** | CEB | Celukan Bawang | Pelabuhan |
| **1103** | CLG | Calang | Pelabuhan |
| **1104** | CLI | Calabai | Pelabuhan |
| **1105** | CXP | Tanjung Intan/ Cilacap | Pelabuhan |
| **1106** | DAS | Dabo Singkep | Pelabuhan |
| **1107** | DJJ | Jayapura | Pelabuhan |
| **1108** | Pelabuhan\_Pelabuhan Penyeberangan\_Marampit | Marampit | Pelabuhan |
| **1109** | Pelabuhan\_Pelabuhan Penyeberangan\_Kolonodale | Kolonodale | Pelabuhan |
| **1110** | Pelabuhan\_Pelabuhan Penyeberangan\_Kolorai | Kolorai | Pelabuhan |
| **1111** | Pelabuhan\_Pelabuhan Penyeberangan\_Kur | Kur | Pelabuhan |
| **1112** | Pelabuhan\_Pelabuhan Penyeberangan\_Lamteng | Lamteng | Pelabuhan |
| **1113** | Pelabuhan\_Pelabuhan Penyeberangan\_Lembeh | Lembeh | Pelabuhan |
| **1114** | Pelabuhan\_Pelabuhan Penyeberangan\_Likupang | Likupang | Pelabuhan |
| **1115** | Pelabuhan\_Pelabuhan Penyeberangan\_Maccini Baji | Maccini Baji | Pelabuhan |
| **1116** | Pelabuhan\_Pelabuhan Penyeberangan\_Makalehi | Makalehi | Pelabuhan |
| **1117** | Pelabuhan\_Pelabuhan Penyeberangan\_Mamuju | Mamuju | Pelabuhan |
| **1118** | Pelabuhan\_Pelabuhan Penyeberangan\_Mangaran | Mangaran | Pelabuhan |
| **1119** | Pelabuhan\_Pelabuhan Penyeberangan\_Kaukes | Kaukes | Pelabuhan |
| **1120** | Pelabuhan\_Pelabuhan Penyeberangan\_Marisa | Marisa | Pelabuhan |
| **1121** | Pelabuhan\_Pelabuhan Penyeberangan\_Melonguane | Melonguane | Pelabuhan |
| **1122** | Pelabuhan\_Pelabuhan Penyeberangan\_Mengkapan | Mengkapan | Pelabuhan |
| **1123** | Pelabuhan\_Pelabuhan Penyeberangan\_Miangas | Miangas | Pelabuhan |
| **1124** | Pelabuhan\_Pelabuhan Penyeberangan\_Muara | Muara | Pelabuhan |
| **1125** | Pelabuhan\_Pelabuhan Penyeberangan\_Musi | Musi | Pelabuhan |
| **1126** | Pelabuhan\_Pelabuhan Penyeberangan\_Nias | Nias | Pelabuhan |
| **1127** | Pelabuhan\_Pelabuhan Penyeberangan\_Nunukan | Nunukan | Pelabuhan |
| **1128** | Pelabuhan\_Pelabuhan Penyeberangan\_Nusa Laut | Nusa Laut | Pelabuhan |
| **1129** | Pelabuhan\_Pelabuhan Penyeberangan\_Onan Runggu (Onanrungu) | Onan Runggu (Onanrungu) | Pelabuhan |
| **1130** | Pelabuhan\_Pelabuhan Penyeberangan\_Holat/Banda Eli | Holat/Banda Eli | Pelabuhan |
| **1131** | Pelabuhan\_Pelabuhan Penyeberangan\_Biaro | Biaro | Pelabuhan |
| **1132** | Pelabuhan\_Pelabuhan Penyeberangan\_Boniton | Boniton | Pelabuhan |
| **1133** | Pelabuhan\_Pelabuhan Penyeberangan\_Bromsi | Bromsi | Pelabuhan |
| **1134** | Pelabuhan\_Pelabuhan Penyeberangan\_Calang | Calang | Pelabuhan |
| **1135** | Pelabuhan\_Pelabuhan Penyeberangan\_Ciwandan | Ciwandan | Pelabuhan |
| **1136** | Pelabuhan\_Pelabuhan Penyeberangan\_Dabo | Dabo | Pelabuhan |
| **1137** | Pelabuhan\_Pelabuhan Penyeberangan\_Dodola | Dodola | Pelabuhan |
| **1138** | Pelabuhan\_Pelabuhan Penyeberangan\_Dumai | Dumai | Pelabuhan |
| **1139** | Pelabuhan\_Pelabuhan Penyeberangan\_Elat | Elat | Pelabuhan |
| **1140** | Pelabuhan\_Pelabuhan Penyeberangan\_Gresik | Gresik | Pelabuhan |
| **1141** | Pelabuhan\_Pelabuhan Penyeberangan\_Paciran | Paciran | Pelabuhan |
| **1142** | Pelabuhan\_Pelabuhan Penyeberangan\_Jampea | Jampea | Pelabuhan |
| **1143** | Pelabuhan\_Pelabuhan Penyeberangan\_Jangkar | Jangkar | Pelabuhan |
| **1144** | Pelabuhan\_Pelabuhan Penyeberangan\_Juata Tarakan | Juata Tarakan | Pelabuhan |
| **1145** | Pelabuhan\_Pelabuhan Penyeberangan\_Kabuena | Kabuena | Pelabuhan |
| **1146** | Pelabuhan\_Pelabuhan Penyeberangan\_Kaimana | Kaimana | Pelabuhan |
| **1147** | Pelabuhan\_Pelabuhan Penyeberangan\_Kamal | Kamal | Pelabuhan |
| **1148** | Pelabuhan\_Pelabuhan Penyeberangan\_Kampung Balak | Kampung Balak | Pelabuhan |
| **1149** | Pelabuhan\_Pelabuhan Penyeberangan\_Kangean | Kangean | Pelabuhan |
| **1150** | Pelabuhan\_Pelabuhan Penyeberangan\_Karimun Jawa | Karimun Jawa | Pelabuhan |
| **1151** | Pelabuhan\_Pelabuhan Penyeberangan\_Wunlah | Wunlah | Pelabuhan |
| **1152** | Pelabuhan\_Pelabuhan Penyeberangan\_Tanjung Phising | Tanjung Phising | Pelabuhan |
| **1153** | Pelabuhan\_Pelabuhan Penyeberangan\_Tanjung Selor | Tanjung Selor | Pelabuhan |
| **1154** | Pelabuhan\_Pelabuhan Penyeberangan\_Teluk Bara | Teluk Bara | Pelabuhan |
| **1155** | Pelabuhan\_Pelabuhan Penyeberangan\_Teluk Gurita | Teluk Gurita | Pelabuhan |
| **1156** | Pelabuhan\_Pelabuhan Penyeberangan\_Ujung | Ujung | Pelabuhan |
| **1157** | Pelabuhan\_Pelabuhan Penyeberangan\_Ulee Lheu | Ulee Lheu | Pelabuhan |
| **1158** | Pelabuhan\_Pelabuhan Penyeberangan\_Wahai | Wahai | Pelabuhan |
| **1159** | Pelabuhan\_Pelabuhan Penyeberangan\_Waisala | Waisala | Pelabuhan |
| **1160** | Pelabuhan\_Pelabuhan Penyeberangan\_Wasior (Sewandaimuni) | Wasior (Sewandaimuni) | Pelabuhan |
| **1161** | Pelabuhan\_Pelabuhan Penyeberangan\_Wika Beton | Wika Beton | Pelabuhan |
| **1162** | Pelabuhan\_Pelabuhan Penyeberangan\_Tanjung Balai Karimun | Tanjung Balai Karimun | Pelabuhan |
| **1163** | AHI | Amahai | Pelabuhan |
| **1164** | PGM | Pagimana | Pelabuhan |
| **1165** | PGX | Pangkal Balam | Pelabuhan |
| **1166** | AGS | Agats | Pelabuhan |
| **1167** | PIN | Panipahan | Pelabuhan |
| **1168** | PIO | Pattirobajo | Pelabuhan |
| **1169** | PJB | Jampea | Pelabuhan |
| **1170** | AAU | Atapupu | Pelabuhan |
| **1171** | PKS | Pangkalan Susu | Pelabuhan |
| **1172** | Pelabuhan\_Pelabuhan Penyeberangan\_Sei Jepun | Sei Jepun | Pelabuhan |
| **1173** | Pelabuhan\_Pelabuhan Penyeberangan\_Penagih | Penagih | Pelabuhan |
| **1174** | Pelabuhan\_Pelabuhan Penyeberangan\_Pomako | Pomako | Pelabuhan |
| **1175** | Pelabuhan\_Pelabuhan Penyeberangan\_Pulau Baai | Pulau Baai | Pelabuhan |
| **1176** | Pelabuhan\_Pelabuhan Penyeberangan\_Pulau Banyak | Pulau Banyak | Pelabuhan |
| **1177** | Pelabuhan\_Pelabuhan Penyeberangan\_Pulau Bunyu | Pulau Bunyu | Pelabuhan |
| **1178** | Pelabuhan\_Pelabuhan Penyeberangan\_Pulau Raas | Pulau Raas | Pelabuhan |
| **1179** | Pelabuhan\_Pelabuhan Penyeberangan\_Rupat | Rupat | Pelabuhan |
| **1180** | Pelabuhan\_Pelabuhan Penyeberangan\_Sabutung | Sabutung | Pelabuhan |
| **1181** | Pelabuhan\_Pelabuhan Penyeberangan\_Sapeken | Sapeken | Pelabuhan |
| **1182** | Pelabuhan\_Pelabuhan Penyeberangan\_Sebatik/Sei Nyamuk | Sebatik/Sei Nyamuk | Pelabuhan |
| **1183** | MKW | Manokwari | Pelabuhan |
| **1184** | Pelabuhan\_Pelabuhan Penyeberangan\_Sei Selari | Sei Selari | Pelabuhan |
| **1185** | Pelabuhan\_Pelabuhan Penyeberangan\_Serwatu | Serwatu | Pelabuhan |
| **1186** | Pelabuhan\_Pelabuhan Penyeberangan\_Siau (Ulu Siau) | Siau (Ulu Siau) | Pelabuhan |
| **1187** | Pelabuhan\_Pelabuhan Penyeberangan\_Sibolga | Sibolga | Pelabuhan |
| **1188** | Pelabuhan\_Pelabuhan Penyeberangan\_Sikabaluan | Sikabaluan | Pelabuhan |
| **1189** | Pelabuhan\_Pelabuhan Penyeberangan\_Sikeli | Sikeli | Pelabuhan |
| **1190** | Pelabuhan\_Pelabuhan Penyeberangan\_Singkil | Singkil | Pelabuhan |
| **1191** | Pelabuhan\_Pelabuhan Penyeberangan\_Taam | Taam | Pelabuhan |
| **1192** | Pelabuhan\_Pelabuhan Penyeberangan\_Tagulandang | Tagulandang | Pelabuhan |
| **1193** | NRE | Namrole | Pelabuhan |
| **1194** | AMB | Ambarita | Pelabuhan |
| **1195** | MKX | Makian | Pelabuhan |
| **1196** | MUO | Muntok (Tanjung Kalian) | Pelabuhan |
| **1197** | NAM | Namlea | Pelabuhan |
| **1198** | MKW | Marampa | Pelabuhan |
| **1199** | NBX | Nabire | Pelabuhan |
| **1200** | NDA | Bandanaira | Pelabuhan |
| **1201** | NIP | Nipah Panjang | Pelabuhan |
| **1202** | NPE | Nusa Penida | Pelabuhan |
| **1203** | MSK | Muara Sabak | Pelabuhan |
| **1204** | NTI | Bintuni | Pelabuhan |
| **1205** | OOS | Ogoamas | Pelabuhan |
| **1206** | ORA | Oransbari | Pelabuhan |
| **1207** | PAH | Paloh | Pelabuhan |
| **1208** | PBI | Padang Bai | Pelabuhan |
| **1209** | AJB | Ajibata | Pelabuhan |
| **1210** | PDB | Padang Bai | Pelabuhan |
| **1211** | PDR | Pangandaran | Pelabuhan |
| **1212** | ARO | Arar | Pelabuhan |
| **1213** | MNG | Mangole | Pelabuhan |
| **1214** | APN | Ampana | Pelabuhan |
| **1215** | MOF | Maumere | Pelabuhan |
| **1216** | MOT | Marapokot | Pelabuhan |
| **1217** | AMQ | Ambon | Pelabuhan |
| **1218** | MOT | Moti | Pelabuhan |
| **1219** | MLW | Molawe | Pelabuhan |
| **1220** | MRA | Marunda | Pelabuhan |
| **1221** | ARN | Parepare | Pelabuhan |
| **1222** | OBI | Obi (Laiwui) | Pelabuhan |
| **1223** | MLI | Malili | Pelabuhan |
| **1224** | MRK | Merak | Pelabuhan |
| **1225** | AUG | Amurang | Pelabuhan |
| **1226** | AMP | Ampana | Pelabuhan |
| **1227** | MLH | Malahayati | Pelabuhan |
| **1228** | MLD | Tarakan | Pelabuhan |
| **1229** | MSI | Masalembo | Pelabuhan |
| **1230** | MSJ | Mesuji | Pelabuhan |
| **1231** | Pelabuhan\_Pelabuhan Penyeberangan\_Amurang | Amurang | Pelabuhan |
| **1232** | Pelabuhan\_Pelabuhan Penyeberangan\_BBJ Bojonegara | BBJ Bojonegara | Pelabuhan |
| **1233** | Pelabuhan\_Pelabuhan Penyeberangan\_Agats | Agats | Pelabuhan |
| **1234** | Pelabuhan\_Pelabuhan Penyeberangan\_Bawean | Bawean | Pelabuhan |
| **1235** | Pelabuhan\_Pelabuhan Laut\_Raja Ampat | Raja Ampat | Pelabuhan |
| **1236** | Pelabuhan\_Pelabuhan Penyeberangan\_Bandar Bakau Jaya (BBJ) | Bandar Bakau Jaya (BBJ) | Pelabuhan |
| **1237** | Pelabuhan\_Pelabuhan Penyeberangan\_Air Putih | Air Putih | Pelabuhan |
| **1238** | Pelabuhan\_Pelabuhan Penyeberangan\_Atsj | Atsj | Pelabuhan |
| **1239** | Pelabuhan\_Pelabuhan Penyeberangan\_Alai Insit | Alai Insit | Pelabuhan |
| **1240** | PEI | Tanjung Silopo | Pelabuhan |
| **1241** | B992 | CIAWI | Terminal |
| **1242** | Terminal\_Sangatta | Sangatta | Terminal |
| **1243** | B99 | Terminal Km. 6 Banjarmasin | Terminal |
| **1244** | B855 | Kotabumi | Terminal |
| **1245** | Terminal\_B\_Untung Suropati | Untung Suropati | Terminal |
| **1246** | Terminal\_B\_Trunojoyo | Trunojoyo | Terminal |
| **1247** | B1008 | Kajen | Terminal |
| **1248** | B967 | Sanggu | Terminal |
| **1249** | Terminal\_B\_Sumber | Sumber | Terminal |
| **1250** | Terminal\_B\_Tegalgede | Tegalgede | Terminal |
| **1251** | B853 | Natai Suka | Terminal |
| **1252** | Terminal\_B\_Ragunan | Ragunan | Terminal |
| **1253** | B911 | Leuwiliang Bogor | Terminal |
| **1254** | Terminal\_B\_Pinang Ranti | Pinang Ranti | Terminal |
| **1255** | B1094 | Simpang Priuk | Terminal |
| **1256** | B1081 | Dumai | Terminal |
| **1257** | B810 | Pilangsari | Terminal |
| **1258** | B811 | Purwodadi | Terminal |
| **1259** | B1059 | Cikotok | Terminal |
| **1260** | APS | Amplas | Terminal |
| **1261** | B1013 | MAMUJU TENGAH | Terminal |
| **1262** | B850 | Sido Mulyo | Terminal |
| **1263** | B1035 | Limboto | Terminal |
| **1264** | B1017 | LANCANG KUNING | Terminal |
| **1265** | Terminal\_B\_Randik | Randik | Terminal |
| **1266** | Terminal\_B\_Rinding | Rinding | Terminal |
| **1267** | Terminal\_B\_Ronggo Sukowati | Ronggo Sukowati | Terminal |
| **1268** | Terminal\_B\_Piliang | Piliang | Terminal |
| **1269** | Terminal\_B\_Sungai Kunjang | Sungai Kunjang | Terminal |
| **1270** | B897 | Kembang Joyo \- Pati | Terminal |
| **1271** | B890 | Jepara | Terminal |
| **1272** | B148 | Bakauheni | Terminal |
| **1273** | B891 | Muara Dua | Terminal |
| **1274** | B937 | Singkil (TTB) | Terminal |
| **1275** | B894 | Sidareja | Terminal |
| **1276** | B924 | Ciledug | Terminal |
| **1277** | B895 | Karangpucung | Terminal |
| **1278** | B917 | Klari Karawang | Terminal |
| **1279** | TGL | Tegal | Terminal |
| **1280** | B90 | UBUNG | Terminal |
| **1281** | B903 | Purwantoro | Terminal |
| **1282** | B904 | Kartasura | Terminal |
| **1283** | B905 | Sukoharjo | Terminal |
| **1284** | B915 | Tawangmangu Karanganyar | Terminal |
| **1285** | B91 | Sweta | Terminal |
| **1286** | B914 | Pracimantoro Wonogiri | Terminal |
| **1287** | B10 | Lintas Sumatera | Terminal |
| **1288** | B89 | Buleleng | Terminal |
| **1289** | B941 | SUBULUSSALAM | Terminal |
| **1290** | B946 | Calang | Terminal |
| **1291** | B887 | Cijulang | Terminal |
| **1292** | B886 | Pameungpeuk | Terminal |
| **1293** | B880 | Cileungsi | Terminal |
| **1294** | TGK | Tingkir | Terminal |
| **1295** | B955 | Banjarbaru | Terminal |
| **1296** | B870 | Motoling | Terminal |
| **1297** | AAL | Alang-Alang Lebar | Terminal |
| **1298** | AWJ | Arya Wiraraja | Terminal |
| **1299** | B956 | Kandangan | Terminal |
| **1300** | B857 | Kota Agung | Terminal |
| **1301** | B856 | Metro | Terminal |
| **1302** | B965 | Dangerakko | Terminal |
| **1303** | B308 | Pasar Teluk Kuantan | Terminal |
| **1304** | B175 | Indramayu | Terminal |
| **1305** | B263 | Sei Kunjang | Terminal |
| **1306** | B27 | ARGA MAKMUR BENGKULU | Terminal |
| **1307** | B29 | Lebak Bulus | Terminal |
| **1308** | B296 | Tapak Tuan | Terminal |
| **1309** | B297 | Kuala Simpang | Terminal |
| **1310** | B298 | Sigli | Terminal |
| **1311** | B301 | Tanjung Balai | Terminal |
| **1312** | B188 | Purbalingga | Terminal |
| **1313** | B163 | Cimone | Terminal |
| **1314** | B31 | Rawamangun | Terminal |
| **1315** | B310 | Pasir Pangairan | Terminal |
| **1316** | AJO | Alam Barajo | Terminal |
| **1317** | TPL | Tipalayo | Terminal |
| **1318** | AJS | Arjosari | Terminal |
| **1319** | B317 | Martapura | Terminal |
| **1320** | WAG | W.A. Gara | Terminal |
| **1321** | B203 | Tanjung | Terminal |
| **1322** | B211 | Jember | Terminal |
| **1323** | B214 | Bondowoso | Terminal |
| **1324** | TWA | Tawangalun | Terminal |
| **1325** | B219 | Maospati | Terminal |
| **1326** | B206 | Wates | Terminal |
| **1327** | B22 | Lubuk Linggau | Terminal |
| **1328** | B204 | Bumiayu | Terminal |
| **1329** | TTPG | Pulo Gebang | Terminal |
| **1330** | B322 | Baturaja | Terminal |
| **1331** | B202 | Slawi | Terminal |
| **1332** | B220 | Magetan | Terminal |
| **1333** | TTN | Tirtonadi | Terminal |
| **1334** | B222 | Padangan | Terminal |
| **1335** | B223 | Lamongan | Terminal |
| **1336** | B229 | Caruban | Terminal |
| **1337** | B254 | Sintang | Terminal |
| **1338** | B63 | TERBOYO | Terminal |
| **1339** | B153 | Pasar Minggu | Terminal |
| **1340** | B152 | Blok M | Terminal |
| **1341** | B43 | Kuningan | Terminal |
| **1342** | TMN | Tamanan | Terminal |
| **1343** | B473 | Grogol | Terminal |
| **1344** | B213 | Brawijaya | Terminal |
| **1345** | B1401 | Bubulak | Terminal |
| **1346** | B53 | Banjarnegara | Terminal |
| **1347** | B41 | Singaparna | Terminal |
| **1348** | B644 | Tipo | Terminal |
| **1349** | B130 | Batu Sangkar | Terminal |
| **1350** | B648 | Jombor | Terminal |
| **1351** | B129 | Sawah lunto | Terminal |
| **1352** | B120 | Sidikalang | Terminal |
| **1353** | B111 | Banda Aceh (Baru) | Terminal |
| **1354** | B81 | Denpasar | Terminal |
| **1355** | B11 | Pariaman | Terminal |
| **1356** | B374 | Dompu | Terminal |
| **1357** | B158 | Tarogong/Terminal Pandeglang Baru | Terminal |
| **1358** | B33 | Pulogadung | Terminal |
| **1359** | B330 | Saketi | Terminal |
| **1360** | B157 | Tanjung Priok | Terminal |
| **1361** | TPG | Tanjung Pinggir | Terminal |
| **1362** | B156 | Senen | Terminal |
| **1363** | B338 | Pangandaran | Terminal |
| **1364** | B342 | Rajagaluh | Terminal |
| **1365** | B81 | Sumenep | Terminal |
| **1366** | B376 | Sambas | Terminal |
| **1367** | B155 | Kampung Melayu | Terminal |
| **1368** | B154 | Cililitan | Terminal |
| **1369** | B383 | Timbau | Terminal |
| **1370** | B401 | Paal II | Terminal |
| **1371** | B402 | Tangkoko | Terminal |
| **1372** | B409 | Bungku | Terminal |
| **1373** | SAJ | Selo Aji | Terminal |
| **1374** | KBM | Kebumen | Terminal |
| **1375** | SBL | Sri Bulan | Terminal |
| **1376** | KEF | Kefamenanu | Terminal |
| **1377** | KEF | Kota Kefamenanu | Terminal |
| **1378** | SBG | Sibolga | Terminal |
| **1379** | KJO | Kiliran Jao | Terminal |
| **1380** | KJY | Karya Jaya | Terminal |
| **1381** | KLD | Kalideres | Terminal |
| **1382** | KAS | KH. Ahmad Sanusi | Terminal |
| **1383** | SAB | Sei Ambawang | Terminal |
| **1384** | KNG | Kertonegoro | Terminal |
| **1385** | KPR | Kampung Rambutan | Terminal |
| **1386** | KPT | Kambang Putih | Terminal |
| **1387** | KSW | Kasintuwu | Terminal |
| **1388** | KTW | Kertawangunan | Terminal |
| **1389** | RJW | Rajekwesi | Terminal |
| **1390** | RJB | Rajabasa | Terminal |
| **1391** | IDH | Indihiang | Terminal |
| **1392** | SKN | Ir. Soekarno | Terminal |
| **1393** | GBR | Gambut Barakat | Terminal |
| **1394** | GMT | Guntur Melati | Terminal |
| **1395** | GRA | Giri Adipura | Terminal |
| **1396** | GWN | Giwangan | Terminal |
| **1397** | GYT | Gayatri | Terminal |
| **1398** | HJM | Harjamukti | Terminal |
| **1399** | SIM | Simbuang | Terminal |
| **1400** | LBN | Labuan | Terminal |
| **1401** | ILP | Induk Lumpue | Terminal |
| **1402** | IPM | Induk Pemalang | Terminal |
| **1403** | ISM | Isimu | Terminal |
| **1404** | JJR | Jatijajar | Terminal |
| **1405** | JTI | Jati | Terminal |
| **1406** | JTP | Jati Pariaman | Terminal |
| **1407** | KAG | Kayuagung | Terminal |
| **1408** | SDK | Surodakan | Terminal |
| **1409** | MRK | Merak | Terminal |
| **1410** | MKG | Mangkang | Terminal |
| **1411** | PRP | Poris Plawad | Terminal |
| **1412** | PRB | Purabaya | Terminal |
| **1413** | MLK | Mandala | Terminal |
| **1414** | MLK | Mandala Lebak | Terminal |
| **1415** | AAI | Anak Air | Terminal |
| **1416** | MOD | Bolaang Mongondow | Terminal |
| **1417** | PPW | Petta Pongawai | Terminal |
| **1418** | MGW | Mengwi | Terminal |
| **1419** | MTR | Madya Tarutung | Terminal |
| **1420** | PBY | Purboyo | Terminal |
| **1421** | PCB | Pondok Cabe | Terminal |
| **1422** | PCT | Pacitan | Terminal |
| **1423** | PGB | Pulo Gebang (B) | Terminal |
| **1424** | PNB | Pinang Baris | Terminal |
| **1425** | PKL | Pekalongan | Terminal |
| **1426** | PKT | Pakupatan | Terminal |
| **1427** | LWP | Leuwipanjang | Terminal |
| **1428** | LGS | Langsa | Terminal |
| **1429** | LHT | Regional Lahat (Batay) | Terminal |
| **1430** | LSP | Latenri Sessu Pekkae | Terminal |
| **1431** | PYK | Bandar Raya Payung Sekaki | Terminal |
| **1432** | PYI | Paya Ilang | Terminal |
| **1433** | LSW | Lhokseumawe | Terminal |
| **1434** | PWK | Bulupitu Purwokerto | Terminal |
| **1435** | PWJ | Purworejo | Terminal |
| **1436** | ETR | Entrop | Terminal |
| **1437** | PUW | Puuwatu | Terminal |
| **1438** | LWS | Malalayang | Terminal |
| **1439** | MBR | Mamboro | Terminal |
| **1440** | PTR | Patria | Terminal |
| **1441** | MBU | Muara Bungo | Terminal |
| **1442** | MDK | Mandalika | Terminal |
| **1443** | MDL | Mendolo | Terminal |
| **1444** | PSR | Pasuruan | Terminal |
| **1445** | Terminal\_B\_Cikarang | Cikarang | Terminal |
| **1446** | Terminal\_B\_Kepuhsari | Kepuhsari | Terminal |
| **1447** | Terminal\_B\_Kabanjahe | Kabanjahe | Terminal |
| **1448** | Terminal\_B\_Jatisrono | Jatisrono | Terminal |
| **1449** | Terminal\_B\_Induk Mulyojati | Induk Mulyojati | Terminal |
| **1450** | Terminal\_B\_Haumeni | Haumeni | Terminal |
| **1451** | Terminal\_B\_GUNUNG AYU | GUNUNG AYU | Terminal |
| **1452** | Terminal\_B\_Gagak Rimang | Gagak Rimang | Terminal |
| **1453** | Terminal\_B\_Drs. Prayitno | Drs. Prayitno | Terminal |
| **1454** | Terminal\_B\_Kertajaya | Kertajaya | Terminal |
| **1455** | Terminal\_B\_Cappabungayya | Cappabungayya | Terminal |
| **1456** | Terminal\_B\_Bunder | Bunder | Terminal |
| **1457** | Terminal\_B\_Bukit Surungan | Bukit Surungan | Terminal |
| **1458** | Terminal\_B\_Bontang | Bontang | Terminal |
| **1459** | Terminal\_B\_Bireuen | Bireuen | Terminal |
| **1460** | Terminal\_B\_Bintoro | Bintoro | Terminal |
| **1461** | Terminal\_B\_Betek | Betek | Terminal |
| **1462** | Terminal\_B\_Batu | Batu | Terminal |
| **1463** | Terminal\_B\_Lolowa | Lolowa | Terminal |
| **1464** | Terminal\_B\_Penggung | Penggung | Terminal |
| **1465** | Terminal\_B\_Penggaron | Penggaron | Terminal |
| **1466** | Terminal\_B\_Paser | Paser | Terminal |
| **1467** | Terminal\_B\_Palabuhanratu | Palabuhanratu | Terminal |
| **1468** | Terminal\_B\_Muara Angke | Muara Angke | Terminal |
| **1469** | Terminal\_B\_Manggarai | Manggarai | Terminal |
| **1470** | Terminal\_B\_Madureso | Madureso | Terminal |
| **1471** | Terminal\_B\_Lubuk Harjo | Lubuk Harjo | Terminal |
| **1472** | Terminal\_B\_Arjasa | Arjasa | Terminal |
| **1473** | Terminal\_B\_Lempake | Lempake | Terminal |
| **1474** | Terminal\_B\_Larangan | Larangan | Terminal |
| **1475** | Terminal\_B\_Landungsari | Landungsari | Terminal |
| **1476** | Terminal\_B\_Kutoarjo (TTB) | Kutoarjo (TTB) | Terminal |
| **1477** | Terminal\_B\_Koto Nan Ampek | Koto Nan Ampek | Terminal |
| **1478** | Terminal\_B\_KM 32 Indralaya | KM 32 Indralaya | Terminal |
| **1479** | Terminal\_B\_Klender | Klender | Terminal |
| **1480** | Terminal\_B\_Kesamben | Kesamben | Terminal |
| **1481** | SPA | Simpang Aur | Terminal |
| **1482** | SSB | Samarinda Seberang | Terminal |
| **1483** | BSG | Baranangsiang | Terminal |
| **1484** | BTG | Betung | Terminal |
| **1485** | BTH | Bareh Solok | Terminal |
| **1486** | BTH | Batoh | Terminal |
| **1487** | SPY | Sumer Payung | Terminal |
| **1488** | SPN | Simpang Nangka | Terminal |
| **1489** | BWN | Bawen | Terminal |
| **1490** | BRK | Boroko | Terminal |
| **1491** | BYA | Banyuangga | Terminal |
| **1492** | CHM | Cicaheum | Terminal |
| **1493** | CKP | Cikampek | Terminal |
| **1494** | CKR | Ciakar | Terminal |
| **1495** | CPU | Cepu | Terminal |
| **1496** | DII | Dungingi | Terminal |
| **1497** | DRA | Dara | Terminal |
| **1498** | DSG | Dhaksinarga | Terminal |
| **1499** | BAP | Batu Ampar | Terminal |
| **1500** | Terminal\_B\_Anjuk Ladang | Anjuk Ladang | Terminal |
| **1501** | Terminal\_B\_Ambulu | Ambulu | Terminal |
| **1502** | Terminal\_B\_Aceh Tamiang | Aceh Tamiang | Terminal |
| **1503** | Terminal\_B\_Aceh Selatan | Aceh Selatan | Terminal |
| **1504** | Terminal\_B\_Aceh Barat Daya | Aceh Barat Daya | Terminal |
| **1505** | B997 | Banyuputih | Terminal |
| **1506** | B999 | COMAL | Terminal |
| **1507** | TDR | Tidar | Terminal |
| **1508** | Terminal\_B\_Petanang | Petanang | Terminal |
| **1509** | BBS | Bobot Sari | Terminal |
| **1510** | BGK | Bangkinang | Terminal |
| **1511** | BJR | Banjar | Terminal |
| **1512** | BKN | Batu Kuning | Terminal |
| **1513** | BKO | Bangko | Terminal |
| **1514** | BMD | Bangga Bangun Desa | Terminal |
| **1515** | BMK | Bimoku | Terminal |
| **1516** | SUB | Subang | Terminal |

	**5.4 Moda**

| NO |  KODE MODA | NAMA MODA |
| ----- | ----- | ----- |
| **1** | **A** | Angkutan Jalan (Bus AKAP) |
| **2** | **B** | Angkutan Jalan (Bus AKDP) |
| **3** | **C** | Angkutan Kereta Api Antarkota |
| **4** | **D** | Angkutan Kereta Api KCJB |
| **5** | **E** | Angkutan Kereta Api Perkotaan |
| **6** | **F** | Angkutan Laut |
| **7** | **G** | Angkutan Penyeberangan |
| **8** | **H** | Angkutan Udara |
| **9** | **I** | Mobil Pribadi |
| **10** | **J** | Motor Pribadi |
| **11** | **K** | Sepeda |

