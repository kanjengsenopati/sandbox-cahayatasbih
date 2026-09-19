# Git Branching Rules (Team Policy)

Saat melakukan tugas yang mengharuskan Anda (Antigravity AI) menulis kode, membuat fitur, atau menambal bug, Anda **WAJIB** mematuhi aturan Git berikut sebelum melakukan _commit_ dan _push_:

## 1. Format Penamaan Branch
* Dilarang keras melakukan _commit_ dan _push_ langsung ke branch `main` atau `staging` secara sepihak.
* Anda harus membuat _branch_ baru yang ditarik dari `staging`.
* Format nama branch harus selalu: `feat/<nama-user>/<nama-fitur-singkat>`
  * **Daftar User Tim Resmi:**
    1. `siswanto` (Lead / Default untuk _environment_ ini)
    2. `anjarabidin`
    3. `arsito-ari`
  * _Jika Anda (AI) sedang membantu pengguna di environment ini dan tidak ada instruksi nama spesifik, gunakan **siswanto** secara default._
  * Contoh: `feat/siswanto/fix-laporan-excel` atau `feat/anjarabidin/tambah-tombol-pos`

## 2. Alur Kerja (Workflow) AI
1. Pastikan repositori lokal tersinkronisasi dengan `staging` terbaru (`git checkout staging && git pull origin staging`).
2. Buat dan pindah ke branch fitur pengguna (`git checkout -b feat/<nama-user>/<nama-fitur>`).
3. Lakukan penulisan kode sesuai instruksi pengguna.
4. Lakukan `git add` dan `git commit` dengan pesan yang jelas.
5. Lakukan _push_ ke repositori jarak jauh: `git push -u origin feat/<nama-user>/<nama-fitur>`.
6. Beritahu pengguna bahwa _push_ berhasil dan **Lead (Ketua Tim)** akan me-_review_ dan membuat Pull Request untuk menggabungkannya ke `staging`/`main`.
