UPDATE booking SET nomor_antrean = CONCAT('A-', DATE_FORMAT(tanggal_booking,'%d%m%y'), '-', LPAD(urutan,3,'0'));

ALTER TABLE booking ADD UNIQUE KEY booking_nomor_antrean_unique (nomor_antrean);