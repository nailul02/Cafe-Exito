# Task 5.6 — Accessibility Basic Checks

**Status: NOT TESTED (browser automation host unavailable).** Chrome dan Edge terpasang, tetapi tidak ada runner/host untuk menilai keyboard navigation, focus order/visibility, modal focus handling, screen reader behavior, atau contrast pada viewport nyata.

Vitest/jsdom 26/26 lulus, tetapi suite itu menggunakan mock dan tidak dilaporkan sebagai accessibility audit. Label input, alt text, button semantics, error announcements, focus visibility, touch target, and contrast belum diperiksa secara sistematis pada halaman-halaman utama.

Rencana setelah runner disetujui: lakukan keyboard-only pass pada Login, customer cart/payment/order status, Kasir transaction, management forms, serta Barista/Kitchen queue; audit label/accessible name, alt text, focus trap/restore, error link/summary, contrast, dan target ukuran sentuh. Browser test tetap harus memakai data/credential nonaktif.
