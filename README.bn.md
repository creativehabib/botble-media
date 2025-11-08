# বটব্ল মিডিয়া ম্যানেজার (Laravel এর জন্য)

Botble Media হলো Laravel 10 এবং 11 অ্যাপ্লিকেশনের জন্য তৈরি একটি পূর্ণাঙ্গ মিডিয়া লাইব্রেরি প্যাকেজ। এটি ফাইল আপলোড, ফোল্ডার ব্রাউজিং, থাম্বনেইল জেনারেশন, এবং Amazon S3, DigitalOcean Spaces, Wasabi, Backblaze B2, BunnyCDN-এর মত ক্লাউড ড্রাইভারের সাথে ইন্টিগ্রেশনসহ একটি সম্পূর্ণ UI সরবরাহ করে।

## প্রয়োজনীয়তা

- PHP 8.1 বা এর নতুন ভার্সন
- Laravel 10 অথবা 11
- Laravel এর Eloquent ORM সমর্থন করে এমন একটি ডাটাবেস
- ফ্রন্টএন্ড অ্যাসেট পুনরায় কম্পাইল করতে চাইলে Node/npm (প্রি-কম্পাইলড অ্যাসেট ইতোমধ্যে অন্তর্ভুক্ত)

## ১. প্যাকেজ ইন্সটল করুন

```bash
composer require botblemedia/media-manager
```

প্যাকেজটির সার্ভিস প্রোভাইডার এবং `RvMedia` ফ্যাসাড `composer.json` এর মাধ্যমে স্বয়ংক্রিয়ভাবে রেজিস্টার হয়, তাই আপনাকে আলাদা করে যোগ করতে হবে না।【F:composer.json†L36-L44】

## ২. অ্যাসেট ও কনফিগারেশন প্রকাশ করুন

ইন্সটলের পর নিম্নলিখিত কমান্ডগুলো চালিয়ে কনফিগ, ভিউ, অ্যাসেট ও অনুবাদ ফাইলগুলো আপনার প্রজেক্টে কপি করুনঃ

```bash
php artisan vendor:publish --provider="Botble\\Media\\Providers\\MediaServiceProvider" --tag=BotbleMedia-media-config
php artisan vendor:publish --provider="Botble\\Media\\Providers\\MediaServiceProvider" --tag=BotbleMedia-media-translations
php artisan vendor:publish --provider="Botble\\Media\\Providers\\MediaServiceProvider" --tag=BotbleMedia-media-views
php artisan vendor:publish --provider="Botble\\Media\\Providers\\MediaServiceProvider" --tag=BotbleMedia-media-assets
```

কনফিগারেশন ফাইলগুলো `core/media/media.php` এবং `config/media.php` উভয় জায়গায় মার্জ হয়, ফলে আপনার সুবিধামত যেকোনো স্থানে সেটিংস ম্যানেজ করতে পারবেন।【F:src/Base/Traits/LoadAndPublishDataTrait.php†L25-L54】【F:src/Providers/MediaServiceProvider.php†L82-L118】

## ৩. মাইগ্রেশন চালান

প্যাকেজে মিডিয়া টেবিলের জন্য পূর্বনির্ধারিত মাইগ্রেশন রয়েছে। কনফিগারেশন প্রকাশের পর এগুলো রান করুনঃ

```bash
php artisan migrate
```

`database/migrations` ডিরেক্টরিতে থাকা ফাইলগুলোতে মিডিয়া সম্পর্কিত টেবিল ও কলাম আপডেটের সব স্ক্রিপ্ট রয়েছে। উদাহরণস্বরূপ, ফোল্ডারের রঙ ও ফাইলের visibility কলামের আপডেট এখানে সংরক্ষিত।【F:database/migrations/2024_05_12_091229_add_column_visibility_to_table_media_files.php†L1-L41】【F:database/migrations/2023_12_07_095130_add_color_column_to_media_folders_table.php†L1-L34】

## ৪. রাউটিং ও অ্যাক্সেস কনফিগার করুন

ডিফল্টভাবে মিডিয়া UI `/media` রুটে পাওয়া যায় এবং `web` ও `auth` middleware দ্বারা সুরক্ষিত থাকে। কনফিগ ফাইল (`config/media.php`) পাবলিশ করার পর এখান থেকে রুট প্রিফিক্স বা middleware স্ট্যাক পরিবর্তন করতে পারবেন।【F:config/media.php†L4-L33】

এছাড়াও প্যাকেজটি গ্র্যানুলার পারমিশন ফ্ল্যাগ (`files.create`, `folders.destroy` ইত্যাদি) প্রদান করে যা আপনার অ্যাপ্লিকেশনের অথরাইজেশন স্তরের সাথে ম্যাপ করতে পারবেন।【F:config/permissions.php†L1-L38】

## ৫. স্টোরেজ ড্রাইভার ও পরিবেশ ভেরিয়েবল

`RvMedia` এর `media_driver` সেটিং (ডিফল্ট `public`) এর মাধ্যমে সক্রিয় ফাইল সিস্টেম ডিস্ক নির্ধারণ করা হয়।【F:src/RvMedia.php†L1194-L1200】 সার্ভিস প্রোভাইডার বুট হওয়ার সময় কনফিগারেশন সিঙ্ক হয় এবং Laravel-এর ডিফল্ট স্টোরেজ সেটিং মিডিয়া ডিস্কের সাথে মিলিয়ে আপডেট হয়।【F:src/Providers/MediaServiceProvider.php†L118-L178】

S3 কমপ্যাটিবল ড্রাইভার ব্যবহার করলে `.env` বা প্যাকেজ সেটিংস (`media_aws_*`, `media_do_spaces_*`, `media_backblaze_*` ইত্যাদি) এ প্রয়োজনীয় ক্রেডেনশিয়াল দিন। Wasabi ও BunnyCDN এর জন্য বিশেষ অ্যাডাপ্টার প্যাকেজটি স্বয়ংক্রিয়ভাবে নিবন্ধন করে।【F:src/Providers/MediaServiceProvider.php†L124-L178】

যদি আপলোডগুলো `public/` ডিরেক্টরিতে রাখতে চান তবে `RV_MEDIA_USE_STORAGE_SYMLINK=false` (ডিফল্ট মান) রাখুন। স্টোরেজ লিংক ব্যবহার করতে চাইলে মান `true` করে দিন এবং Laravel এর `storage:link` কমান্ড চালান।【F:config/media.php†L108-L112】

## ৬. ভিউতে মিডিয়া UI অন্তর্ভুক্ত করুন

`resources/views/vendor/core/media/index.blade.php` ফাইলে সম্পূর্ণ মিডিয়া ম্যানেজার ভিউ রয়েছে, যেখানে হেডার ও ফুটার অ্যাসেট এবং কন্টেন্ট প্যানেল `RvMedia` ফ্যাসাডের হেল্পার মেথডের মাধ্যমে রেন্ডার হয়।【F:resources/views/index.blade.php†L1-L12】 আপনি চাইলে এই ভিউ সরাসরি রিটার্ন করতে পারেন, অথবা নিজস্ব ব্লেড লেআউটে `RvMedia::renderHeader()`, `RvMedia::renderContent()`, এবং `RvMedia::renderFooter()` এম্বেড করতে পারেন।

এডিটর বা ফর্মে পপআপ হিসেবে ব্যবহার করতে চাইলে `/media/popup` রুট ব্যবহার করুন, যা এম্বেডেবল UI প্রদান করে।【F:routes/web.php†L5-L39】

## ৭. ঐচ্ছিক ফিচারসমূহ

- **চাংকড আপলোড**: বড় ফাইল আপলোডের জন্য `RV_MEDIA_UPLOAD_CHUNK=true` সেট করুন এবং কনফিগে চাংক সাইজ/সর্বোচ্চ ফাইল সাইজ সামঞ্জস্য করুন। নির্ধারিত কমান্ড সক্রিয় থাকলে পুরোনো চাংক ফাইল স্বয়ংক্রিয়ভাবে পরিষ্কার হবে।【F:config/media.php†L74-L105】【F:src/Providers/MediaServiceProvider.php†L180-L208】
- **ডকুমেন্ট প্রিভিউ**: `RV_MEDIA_DOCUMENT_PREVIEW_ENABLED` দ্বারা Google/Microsoft ডকুমেন্ট প্রিভিউ চালু বা বন্ধ করুন এবং `RV_MEDIA_DOCUMENT_PREVIEW_PROVIDER` দিয়ে কোন প্রোভাইডার ব্যবহার করবেন তা নির্ধারণ করুন।【F:config/media.php†L106-L137】
- **ওয়াটারমার্ক ও থাম্বনেইল**: একই কনফিগ ফাইল থেকে ওয়াটারমার্কের সোর্স, অপাসিটি, অবস্থান এবং থাম্বনেইল জেনারেশনের সেটিংস নিয়ন্ত্রণ করুন।【F:config/media.php†L56-L73】【F:config/media.php†L138-L144】

## ৮. Artisan কমান্ড

কনসোল মোডে চলাকালে সার্ভিস প্রোভাইডারটি থাম্বনেইল জেনারেশন, ক্রপিং, ওয়াটারমার্ক যোগ এবং চাংক ফাইল ক্লিয়ার করার জন্য একাধিক artisan কমান্ড নিবন্ধন করে। এগুলো প্যাকেজ ইন্সটল হওয়ার সাথে সাথেই ব্যবহারযোগ্য এবং প্রয়োজনে শিডিউল করা যায়।【F:src/Providers/MediaServiceProvider.php†L180-L208】

এই ধাপগুলো অনুসরণ করলে আপনার বিদ্যমান Laravel প্রজেক্টে Botble Media সহজেই সংযুক্ত করতে পারবেন এবং প্রয়োজন অনুযায়ী স্টোরেজ, অথরাইজেশন ও UI কাস্টমাইজ করতে পারবেন।
