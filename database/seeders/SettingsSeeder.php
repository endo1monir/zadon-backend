<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'policy_ar',
                'value' => "سياسة الخصوصية لموقع زادون\n\n"
                    ."نلتزم في زادون بحماية خصوصية مستخدمينا وبياناتهم الشخصية. توضح هذه السياسة كيفية جمع بياناتك واستخدامها وحمايتها عند استخدامك لتطبيقنا.\n\n"
                    ."أولاً: البيانات التي نجمعها\n"
                    ."نجمع البيانات التي تقدمها مباشرة مثل الاسم ورقم الجوال والبريد الإلكتروني، بالإضافة إلى بيانات استخدام التطبيق مثل سجل الطلبات والعناوين.\n\n"
                    ."ثانياً: استخدام البيانات\n"
                    ."تُستخدم بياناتك لتقديم الخدمات ومعالجة الطلبات وتحسين تجربتك وتوفير الدعم الفني وإشعارات الحساب، ولا نبيع بياناتك لأي طرف ثالث.\n\n"
                    ."ثالثاً: حماية البيانات\n"
                    ."نتخذ إجراءات أمنية وتقنية مناسبة لحماية بياناتك من الوصول غير المصرح به أو التعديل أو الإفصاح.\n\n"
                    ."رابعاً: حفظ البيانات\n"
                    ."نحتفظ ببياناتك فقط للمدة اللازمة لتحقيق الأغراض المذكورة في هذه السياسة ووفقاً للمتطلبات القانونية.\n\n"
                    ."خامساً: حقوقك\n"
                    ."يحق لك طلب الاطلاع على بياناتك أو تصحيحها أو حذفها في أي وقت من خلال التواصل معنا عبر قنوات الدعم.\n\n"
                    .'آخر تحديث: 1 يناير 2026',
            ],
            [
                'key' => 'policy_en',
                'value' => "Zadon Privacy Policy\n\n"
                    ."We at Zadon are committed to protecting the privacy of our users and their personal data. This policy explains how we collect, use, and protect your data when you use our application.\n\n"
                    ."1. Data We Collect\n"
                    ."We collect data you provide directly, such as your name, phone number, and email address, as well as usage data such as order history and addresses.\n\n"
                    ."2. How We Use Your Data\n"
                    ."Your data is used to provide our services, process orders, improve your experience, provide technical support, and send account notifications. We never sell your data to third parties.\n\n"
                    ."3. Data Protection\n"
                    ."We apply appropriate security and technical measures to protect your data from unauthorised access, alteration, or disclosure.\n\n"
                    ."4. Data Retention\n"
                    ."We retain your data only for as long as necessary to fulfil the purposes described in this policy and in line with legal requirements.\n\n"
                    ."5. Your Rights\n"
                    ."You may request access to, correction of, or deletion of your data at any time by contacting us through our support channels.\n\n"
                    .'Last updated: January 1, 2026',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], ['value' => $setting['value']]);
        }
    }
}
