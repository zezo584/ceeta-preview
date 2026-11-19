=== CEETA Petroleum & Engineering — WordPress Theme ===
Version: 1.0.1 · Requires WordPress 6.0+ · PHP 7.4+

------------------------------------------------------------------
العربية — طريقة التركيب
------------------------------------------------------------------
1) لوحة التحكم ← المظهر ← قوالب ← أضف جديد ← رفع قالب ← اختر ملف ceeta-wordpress-theme.zip ← تثبيت ← تفعيل.
2) عند التفعيل ينشئ القالب تلقائيًا كل الصفحات (الرئيسية، من نحن، الخدمات + 7 صفحات خدمات، الأكاديمية، الشهادات، تواصل معنا)،
   ويضبط الصفحة الرئيسية والروابط الدائمة (/%postname%/).
3) النسخة الإنجليزية هي الأساسية على /  والنسخة العربية تلقائيًا على /ar/  (مثال: /ar/services/tubular-inspection/).
   لا تحتاج أي إضافة ترجمة.
4) لو ظهرت صفحة 404 على روابط /ar/ : الإعدادات ← الروابط الدائمة ← حفظ التغييرات (مرة واحدة).

التعديل:
- المظهر ← تخصيص ← CEETA Options:
  • الإيميل والتليفون والواتساب والعنوان ورابط/خريطة جوجل (تظهر في الشريط العلوي والفوتر وصفحة التواصل).
  • روابط فيسبوك ولينكدإن وإنستجرام.
  • صور سلايدر الهيرو (5 صور).
  • الإيميل اللي بتوصله رسائل النموذج (الافتراضي: services@ceeta-eps.com).
  • عنوان ووصف SEO للصفحة الرئيسية (عربي/إنجليزي) + كود Google Search Console.
- صور سلايدر التدريب (الرئيسية + الأكاديمية): الصفحات ← CEETA Academy ← صندوق "Training Gallery" ← اختر الصور ← تحديث.
  نص الـ Alt للصورة بيظهر كتعليق.
- أيقونة الموقع (Favicon) = شعار CEETA تلقائيًا، ويمكن تغييرها من تخصيص ← هوية الموقع.
- نصوص الصفحات موجودة في مجلد القالب:  parts/en/*.php  (إنجليزي)  و  parts/ar/*.php  (عربي).

النموذج (Request a Quote):
- كل استفسار يتبعت على الإيميل ويتحفظ كمان في لوحة التحكم ← Inquiries (حتى لو الاستضافة مش بتبعت إيميلات).
- يوجد زر واتساب بجانب زر الإرسال يفتح المحادثة برسالة جاهزة.
- يُنصح بتثبيت إضافة WP Mail SMTP لضمان وصول الإيميلات.

SEO المدمج (بدون إضافات):
- عنوان ووصف لكل صفحة بالعربي والإنجليزي، canonical، hreflang (en / ar / x-default)، Open Graph و Twitter Cards.
- بيانات منظمة JSON-LD: Organization / ProfessionalService، WebSite، WebPage، BreadcrumbList، Service لكل خدمة، و Course لكل دورة.
- خريطة موقع ثنائية اللغة:  /ceeta-sitemap.xml  (مضافة تلقائيًا إلى robots.txt) — قدّمها في Google Search Console.
- لا تحتاج Yoast أو Rank Math؛ ولو مثبتين، القالب يوقف مخرجاتهم على صفحات CEETA لمنع التكرار.

------------------------------------------------------------------
English — Quick start
------------------------------------------------------------------
1) Appearance → Themes → Add New → Upload Theme → ceeta-wordpress-theme.zip → Install → Activate.
2) Activation creates every page (EN + AR), sets the static front page and pretty permalinks.
   English lives at /, Arabic at /ar/ (e.g. /ar/academy/). If /ar/ URLs return 404, re-save Settings → Permalinks once.
3) Appearance → Customize → CEETA Options: contact details, social links, hero images, form recipient, SEO, preloader.
4) Pages → CEETA Academy → "Training Gallery" box: choose the training photos for the 3D slider (Home + Academy).
5) Inquiries are emailed to services@ceeta-eps.com and stored under Dashboard → Inquiries. Install an SMTP plugin for reliable email delivery.
6) Page texts: parts/en/*.php and parts/ar/*.php. Shared styles: assets/css/style.css. Scripts: assets/js/main.js.

Contact email & form recipient: services@ceeta-eps.com (change anytime in the Customizer).

Before going live — please add:
- Facebook and LinkedIn page URLs (empty until set in the Customizer).
