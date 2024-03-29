<?php

namespace App\Controllers;

use App\Models\DataBuku;
use App\Models\DataGenre;
use App\Models\DataPinjam;
use App\Models\DataAnggota;

class Dashboard extends BaseController
{
    public function __construct()
    {
        $this->dataBuku = new DataBuku();
        $this->dataGenre = new DataGenre();
        $this->dataPinjam = new DataPinjam();
        $this->dataAnggota = new DataAnggota();
    }

    public function index()
    {
        $data = [
            'judul' => 'Dashboard',
            'aktif' => 'dashboard',
            'peminjam' => $this->dataPinjam->getWheres(),
            'jumlah' => $this->dataBuku->countAll(),
            'jumeslah' => $this->dataPinjam->countAll(),
            'jumeslahs' => $this->dataPinjam->where(['setatus' => "Belum kembali"])->countAllResults(),
            'jumelah' => $this->dataAnggota->countAll()
        ];
        return view('Dashboard/index', $data);
    }

    public function riwayat()
    {
        $data = [
            'judul' => 'Riwayat Peminjaman',
            'aktif' => 'riwayat',
            'peminjam' => $this->dataPinjam->getData(),
        ];
        return view('Dashboard/riwayat', $data);
    }
    public function detail($id)
    {
        $data = [
            'judul' => 'Data Buku / Detail',
            'aktif' => 'tabel',
            'buku' => $this->dataBuku->where(['id_buku' => $id])->first()
        ];
        return view('Dashboard/detail', $data);
    }

    public function table()
    {
        $data = [
            'judul' => 'Tables',
            'validation' => \Config\Services::validation(),
            'aktif' => 'tabel',
            'genre' => $this->dataGenre->getData(),
            'buku' => $this->dataBuku->getData()
        ];
        return view('Dashboard/table', $data);
    }

    public function tambah()
    {
        $data = [
            'judul' => 'Tables / Tambah Buku',
            'aktif' => 'tabel',
            'genre' => $this->dataGenre->getData(),
            'validation' => \Config\Services::validation()
        ];
        return view('Dashboard/tambah', $data);
    }

    public function simpan()
    {
        if (!$this->validate([
            'judul' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Judul mohon diisi'
                ]
            ],
            'pengarang' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Pengarang Buku mohon diisi'
                ]
            ],
            'penerbit' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Pengarang Buku mohon diisi'
                ]
            ],
            'tahun_terbit' => [
                'rules' => 'required|decimal',
                'errors' => [
                    'required' => 'Tahun Terbit mohon diisi',
                    'decimal' => 'Tahun Terbit harus berupa angka'
                ]
            ]
        ])) {
            $validation = \Config\Services::validation();
            session()->setFlashdata('errors', $this->validator->listErrors());
            return redirect()->to('/table')->withInput()->with('validation', $validation);
        }

        $cover = $this->request->getFile('cover');
        $kover = $cover->getRandomName();
        $id = rand(100, 10000);
        $tam = "BK_$id";


        $simpan = [
            'id_buku' => $tam,
            'judul' => $this->request->getVar('judul'),
            'penerbit' => $this->request->getVar('penerbit'),
            'pengarang' => $this->request->getVar('pengarang'),
            'tahun_terbit' => $this->request->getVar('tahun_terbit'),
            'genre' => $this->request->getVar('genre'),
            'status' => $this->request->getVar('status'),
            'cover' => $kover
        ];

        $this->dataBuku->insert($simpan);

        $cover->move('cover', $kover);

        session()->setFlashdata('yes', 'Data berhasil ditambah');
        return redirect()->to(base_url() . '/tabel');
    }

    public function edit($id)
    {
        $data = [
            'judul' => 'Tables / Edit Buku',
            'aktif' => 'tabel',
            'buku' => $this->dataBuku->where(['id_buku' => $id])->first(),
            'genre' => $this->dataGenre->getData(),
            'validation' => \Config\Services::validation()
        ];
        return view('Dashboard/edit', $data);
    }

    public function ubah($id)
    {
        if (!$this->validate([
            'judul' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Judul mohon diisi'
                ]
            ],
            'pengarang' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Pengarang Buku mohon diisi'
                ]
            ],
            'genre' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Genre Buku mohon dipilih'
                ]
            ],
            'penerbit' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Pengarang Buku mohon diisi'
                ]
            ],

            'tahun_terbit' => [
                'rules' => 'required|decimal',
                'errors' => [
                    'required' => 'Tahun Terbit mohon diisi',
                    'decimal' => 'Tahun Terbit harus berupa angka'
                ]
            ]
        ])) {
            $validation = \Config\Services::validation();
            session()->setFlashdata('errors', $this->validator->listErrors());
            return redirect()->to('/Dashboard/edit/' . $id)->withInput()->with('validation', $validation);
        }

        $cover = $this->request->getFile('cover');
        $kover = $cover->getRandomName();

        $simpan = [
            'judul' => $this->request->getVar('judul'),
            'pengarang' => $this->request->getVar('pengarang'),
            'penerbit' => $this->request->getVar('penerbit'),
            'tahun_terbit' => $this->request->getVar('tahun_terbit'),
            'genre' => $this->request->getVar('genre'),
            'status' => $this->request->getVar('status'),
            'cover' => $kover
        ];

        $this->dataBuku->update($id, $simpan);

        $cover->move('cover', $kover);

        session()->setFlashdata('ubah', 'Data berhasil diupdate');
        return redirect()->to(base_url() . '/tabel');
    }

    public function delete($id)
    {
        $this->dataBuku->delete($id);

        session()->setFlashdata('hapus', 'Data berhasil dihapus');
        return redirect()->to(base_url() . '/tabel');
    }

    public function kembali($id)
    {
        $data = [
            'setatus' => "Sudah Kembali"
        ];

        $this->dataPinjam->update($id, $data);

        session()->setFlashdata('yu', 'Data berhasil diubah');
        return redirect()->to(base_url() . '/');
    }
}
