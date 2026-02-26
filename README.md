# ⚖️ FineSecure: Confiscation & Billing Management System

**FineSecure** is a specialized financial and administrative tool designed for international boarding schools to manage prohibited items and violation fines. It replaces manual fine-collection with a transparent, automated billing system that tracks every confiscated item from the moment of seizure to its legal release.

---

## 🎯 The Purpose
To uphold institutional discipline by providing a clear, auditable process for managing student violations and the associated financial penalties (confiscation fines) in a professional manner.

---

## 🚀 Key Features
- **Violation Logging:** Record the type of violation, item seized, and the time of confiscation.
- **Automated Fine Calculation:** Built-in logic to calculate fines based on the severity of the violation or the duration of storage.
- **Payment Integration:** Tracks the status of fine payments (Pending, Partial, Paid) via multiple methods.
- **Evidence Management:** Upload descriptions or image references of the confiscated items for accountability.
- **Release Authorization:** A secure workflow where items are only marked "Released" once the financial obligation is cleared.
- **Parental Reports:** Generates a formal summary of the violation and fine for transparent communication with parents.

---

## 🛠️ Tech Stack
- **Backend:** Native PHP (PDO) — For precise financial transaction handling.
- **Database:** MySQL — Relational mapping between Students, Violations, and Billing.
- **Frontend:** Tailwind CSS — Clean, authoritative UI for administrative use.
- **Security:** CSRF Protection and audit logs to prevent unauthorized record tampering.

---

## ⚙️ How it Works
1. **Confiscation:** Staff logs a violation and the system automatically creates a "Pending Fine" in the student's ledger.
2. **Billing:** The system generates a billing statement based on school statutes (e.g., Rp 50.000 for electronic violations).
3. **Settlement:** Once the student/parent pays the fine, the system updates the status to `Settled`.
4. **Return:** The "Release" button becomes active, allowing the supervisor to return the item and close the case.

---

## 🛡️ Strategic Value
This project demonstrates expertise in **Accountability Systems**. It handles sensitive institutional data and financial records, proving that you can build software that stands up to audits and promotes a culture of transparency and discipline.
