# Same-to-Same PDF <-> Word — FREE Setup (works on Hostinger)

The /tools converters pick the best available engine automatically:

1. **Adobe PDF Services API** (recommended — the same engine as Adobe Acrobat,
   exact same-to-same layout). FREE tier: 500 conversions/month, no credit
   card, pure HTTPS from PHP — works on Hostinger shared hosting.
2. LibreOffice / pdf2docx if installed on the machine (optional, VPS only).
3. In-browser converter (always works, no setup — approximated layout).

## Enable the Adobe engine (one-time, ~5 minutes, free)
1. Go to https://developer.adobe.com/document-services/apis/pdf-services/
   and click "Get credentials" (sign in with any free Adobe ID).
2. Choose **PDF Services API** -> create new credentials
   (OAuth Server-to-Server). No credit card needed.
3. Copy the **Client ID** and **Client Secret** from the Developer Console.
4. Add to your `.env` (local AND on Hostinger):

       ADOBE_CLIENT_ID=your_client_id_here
       ADOBE_CLIENT_SECRET=your_client_secret_here

5. Refresh the tool page — it will show "High-fidelity engine active" on
   PDF to Word, Word to PDF, PowerPoint to PDF and Excel to PDF.

Covers: pdf->docx (Export PDF), doc/docx/ppt/pptx/xls/xlsx/rtf -> pdf (Create PDF).
Files are uploaded to Adobe only for the conversion and we delete the asset
right after download. Quota resets monthly (500/month, 25 requests/min).

## Notes
- Without credentials everything still works through the in-browser engine.
- Temp files live in storage/app/tools-convert and are cleaned up automatically.
- Upload limit 50 MB (also bounded by php.ini upload_max_filesize / post_max_size).
