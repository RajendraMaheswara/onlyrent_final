function loadTransactionDetail(transactionId) {
    $('#transactionDetailContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat detail transaksi...</p>
        </div>
    `);

    $.ajax({
        url: 'transaksi.php?action=show&id=' + transactionId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Response:', response); // Debug log
            
            // Periksa jika response kosong atau error
            if (!response || response.error) {
                showErrorModal(response?.error || 'Data transaksi tidak ditemukan');
                return;
            }

            // Pastikan data yang diperlukan ada
            if (!response.id_sewa || !response.nama_barang) {
                showErrorModal('Data transaksi tidak lengkap');
                return;
            }

            const html = buildTransactionDetailHtml(response);
            $('#transactionDetailContent').html(html);
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            console.error('Response Text:', xhr.responseText);
            
            let errorMsg = 'Gagal memuat data. Silakan coba lagi.';
            
            try {
                const errResponse = JSON.parse(xhr.responseText);
                if (errResponse.error) {
                    errorMsg = errResponse.error;
                }
            } catch (e) {
                console.error('Error parsing error response:', e);
            }
            
            showErrorModal(errorMsg);
        }
    });
}

function buildTransactionDetailHtml(data) {
    // Fungsi helper untuk format tanggal
    const formatDate = (dateStr) => {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long', 
            year: 'numeric'
        });
    };

    // Fungsi helper untuk format mata uang
    const formatCurrency = (amount) => {
        if (!amount) return 'Rp 0';
        return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    };

    // Dapatkan class untuk status badge
    const statusClass = getStatusClass(data.status_transaksi);

    return `
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-3">Detail Transaksi</h5>
                
                <div class="mb-4">
                    <h6>Informasi Barang</h6>
                    <div class="d-flex align-items-start">
                        <img src="${data.gambar_barang ? '../../assets/images/barang/' + encodeURIComponent(data.gambar_barang) : 'https://via.placeholder.com/150?text=No+Image'}" 
                             class="rounded me-3"
                             style="width: 100px; height: 100px; object-fit: cover;"
                             onerror="this.src='https://via.placeholder.com/150?text=Gambar+Error'">
                        <div>
                            <h5>${data.nama_barang || '-'}</h5>
                            <p class="text-muted mb-1">ID: ${data.id_barang || '-'}</p>
                            <p>${formatCurrency(data.harga_sewa)}/hari</p>
                        </div>
                    </div>
                    <p class="mt-2">${data.deskripsi || 'Tidak ada deskripsi'}</p>
                </div>
                
                <div>
                    <h6>Detail Sewa</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">ID Transaksi</th>
                            <td>#${data.id_transaksi || '-'}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Sewa</th>
                            <td>${formatDate(data.tanggalSewa)}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Kembali</th>
                            <td>${formatDate(data.tanggalKembali)}</td>
                        </tr>
                        <tr>
                            <th>Durasi</th>
                            <td>${data.durasi || 1} hari</td>
                        </tr>
                        <tr>
                            <th>Total Bayar</th>
                            <td><strong>${formatCurrency(data.totalBayar)}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-4">
                    <h6>Status Transaksi</h6>
                    <div class="d-flex align-items-center">
                        <span class="badge ${statusClass} me-2" style="font-size: 1rem;">
                            ${data.status_text || '-'}
                        </span>
                        ${data.status_transaksi == 0 ? `
                            <a href="transaksi.php?action=cancel&id=${data.id_sewa}" 
                               class="btn btn-sm btn-outline-danger ms-auto"
                               onclick="return confirm('Yakin ingin membatalkan transaksi?')">
                                <i class="fas fa-times"></i> Batalkan
                            </a>
                        ` : ''}
                    </div>
                    ${data.tanggal_transaksi ? `
                        <p class="text-muted small mt-2">
                            Terakhir diperbarui: ${formatDate(data.tanggal_transaksi)}
                        </p>
                    ` : ''}
                </div>
                
                <div class="mb-4">
                    <h6>Bukti Pembayaran</h6>
                    ${data.bukti_pembayaran ? `
                        <div class="text-center">
                            <img src="../../assets/images/transaksi/${encodeURIComponent(data.bukti_pembayaran)}" 
                                 class="img-thumbnail mb-2"
                                 style="max-height: 200px; cursor: pointer;"
                                 onclick="viewPaymentProof('${encodeURIComponent(data.bukti_pembayaran)}')"
                                 onerror="this.src='https://via.placeholder.com/300x200?text=Bukti+Tidak+Valid'">
                            <p class="text-muted small">Klik gambar untuk memperbesar</p>
                        </div>
                    ` : `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Bukti pembayaran belum tersedia
                        </div>
                    `}
                </div>
                
                <div>
                    <h6>Informasi Penyewa</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Nama</th>
                            <td>${data.nama_penyewa || '-'}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>${data.email || '-'}</td>
                        </tr>
                        <tr>
                            <th>Telepon</th>
                            <td>${data.no_telepon || '-'}</td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>${data.alamat || '-'}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    `;
}

// Fungsi untuk menampilkan modal bukti pembayaran
function viewPaymentProof(imageName) {
    if (!imageName) return;
    
    const imgSrc = `../../assets/images/transaksi/${imageName}`;
    $('#paymentProofImage').attr('src', imgSrc)
        .attr('alt', 'Bukti Pembayaran')
        .on('error', function() {
            $(this).attr('src', 'https://via.placeholder.com/600x400?text=Bukti+Tidak+Tersedia');
        });
    
    // Tampilkan modal
    const paymentProofModal = new bootstrap.Modal(document.getElementById('paymentProofModal'));
    paymentProofModal.show();
}

// Fungsi untuk mendapatkan class CSS berdasarkan status
function getStatusClass(status) {
    switch(parseInt(status)) {
        case 0: return 'bg-warning text-dark'; // Pending
        case 1: return 'bg-primary'; // Approved
        case 2: return 'bg-success'; // Completed
        case 3: return 'bg-danger';  // Cancelled
        case 4: return 'bg-secondary'; // Rejected
        default: return 'bg-secondary';
    }
}

// Fungsi untuk menampilkan pesan error
function showErrorModal(message) {
    $('#transactionDetailContent').html(`
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${message || 'Terjadi kesalahan saat memuat detail transaksi. Silakan coba lagi.'}
        </div>
        <div class="text-center mt-3">
            <button class="btn btn-primary" onclick="loadTransactionDetail(${$('#transactionDetailModal').data('currentTransactionId')})">
                <i class="fas fa-sync-alt me-2"></i>Coba Lagi
            </button>
        </div>
    `);
}

// Simpan transactionId saat modal dibuka
$(document).on('show.bs.modal', '#transactionDetailModal', function (e) {
    const button = $(e.relatedTarget);
    const transactionId = button.data('bsTransactionId') || button.closest('tr').find('td:first').text().replace('#','');
    $(this).data('currentTransactionId', transactionId);
});