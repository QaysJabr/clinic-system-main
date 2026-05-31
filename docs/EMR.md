# EMR architecture (Phase 3)

## Overview

The patient file (`/patients/{id}`) is now a **medical record hub**: unified timeline, structured clinical profile, SOAP notes, diagnosis history, categorized attachments, and QR quick lookup—while preserving legacy visit fields and financial profile routes.

## Schema

| Table / column | Purpose |
|----------------|---------|
| `visit_soap_notes` | Structured S/O/A/P per visit (`visit_id` unique) |
| `patient_clinical_records` | Allergies, chronic conditions, meds, family/surgical history, risk flags (`type` enum) |
| `patient_diagnoses` | Searchable diagnosis history; optional `icd_code`, `visit_id`, `doctor_id` |
| `patient_lookup_tokens` | Hashed tokens for QR staff lookup |
| `attachments.category` | `document`, `lab`, `radiology`, `prescription`, `other` |

## Backward compatibility

- Visit fields `chief_complaint`, `diagnosis`, `treatment_plan`, `notes` remain and sync from SOAP on save.
- Existing visits without SOAP rows display legacy field values via `VisitSoapService::resolveForForm()`.
- Financial profile (`/patients/{id}/profile`) unchanged.

## Services

| Service | Role |
|---------|------|
| `PatientTimelineService` | Merges visits, appointments, invoices, payments, diagnoses, attachments into sorted timeline |
| `VisitSoapService` | SOAP CRUD + legacy field mirror |
| `PatientClinicalProfileService` | Clinical record grouping and CRUD |
| `PatientDiagnosisService` | Diagnosis history + auto-record from visit `diagnosis` |
| `PatientQrService` | Issue/resolve lookup tokens |

## Security

- **TenantScope** on `PatientClinicalRecord`, `PatientDiagnosis`, `PatientLookupToken`.
- **PatientPolicy** `viewProfile` / `viewPatientClinical` gate chart and timeline clinical data.
- **AttachmentPolicy** — download/preview requires patient/visit access, not permission alone.
- **AuditLogger** on clinical record and attachment changes.

## Timeline design

Chronological feed (newest first), max ~80 items. Doctors see only their visits on timeline; admins/reception see full financial/clinical mix. Event types: `visit`, `appointment`, `invoice`, `payment`, `diagnosis`, `lab`, `radiology`, `prescription`, `attachment`.

## Routes

| Route | Purpose |
|-------|---------|
| `GET patients/{patient}` | EMR chart (was basic show) |
| `POST patients/{patient}/clinical-records` | Add clinical item |
| `DELETE patients/{patient}/clinical-records/{record}` | Deactivate item |
| `GET patients/{patient}/qr-card` | Printable QR card |
| `GET lookup/patient/{token}` | Staff QR redirect (auth required) |
| `GET attachments/{id}/preview` | Inline PDF/image preview |

## Remaining roadmap

- Vitals (BP, weight, BMI) per visit
- ICD-10 picker UI and coded problem list
- Lab result structured parsing (HL7/FHIR)
- Patient portal read-only chart
- Timeline filters and export PDF
- Attachment virus scan and private disk storage
- Consent forms and e-signature

## Tests

`tests/Feature/PatientEmrTest.php` — profile access, SOAP persistence, clinical records, attachment authorization, QR lookup.
