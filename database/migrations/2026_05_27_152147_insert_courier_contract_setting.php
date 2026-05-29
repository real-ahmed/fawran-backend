<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaultContractTemplate = <<<'HTML'
<div style="direction: rtl; font-family: 'Amiri', 'Tajawal', sans-serif; line-height: 1.8;">
    <h2 style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px;">عقد عمل مندوب توصيل مستقل</h2>
    <p><strong>رقم العقد:</strong> {contract_number}</p>
    <p><strong>تاريخ تحرير العقد:</strong> {date}</p>
    <p>إنه في يوم <strong>{day_name}</strong> الموافق <strong>{date}</strong> تم إبرام هذا العقد بين كل من:</p>
    <p><strong>الطرف الأول (المنصة):</strong> منصة فورا (Fawran) لخدمات التوصيل.</p>
    <p><strong>الطرف الثاني (المندوب):</strong> السيد/ {courier_name}، رقم الهوية: {national_id}، ويحمل رقم هاتف: {phone}</p>
    
    <h3>البند الأول: موضوع العقد</h3>
    <p>يقر الطرف الثاني بموافقته على العمل كمندوب توصيل مستقل عبر منصة الطرف الأول، باستخدام مركبته الخاصة من نوع <strong>{vehicle_type}</strong>، وتوصيل الطلبات للعملاء وفقاً للمعايير والشروط المحددة.</p>

    <h3>البند الثاني: الالتزامات</h3>
    <ul>
        <li>يلتزم الطرف الثاني بتوصيل الطلبات في الوقت المحدد وبحالة جيدة.</li>
        <li>يلتزم الطرف الثاني بالمحافظة على حسن السير والسلوك والتعامل اللائق مع العملاء.</li>
        <li>يتحمل الطرف الثاني المسؤولية الكاملة عن أي مخالفات مرورية أو أضرار تلحق بمركبته أثناء فترة العمل.</li>
    </ul>

    <h3>البند الثالث: العمولات والتسويات</h3>
    <p>يستحق الطرف الأول عمولة متفق عليها من كل طلب توصيل ناجح، ويلتزم الطرف الثاني بتسوية أي مبالغ نقدية (COD) يتم تحصيلها من العملاء لصالح المنصة أو المتاجر وفقاً لسياسة التسوية المعتمدة.</p>

    <h3>البند الرابع: فسخ العقد</h3>
    <p>يحق للطرف الأول إيقاف أو فسخ هذا العقد فوراً في حال ثبوت أي تلاعب، أو تأخير متعمد، أو شكاوى متكررة من العملاء تجاه الطرف الثاني.</p>

    <div style="margin-top: 50px; display: flex; justify-content: space-between;">
        <div style="text-align: center;">
            <p><strong>توقيع الطرف الأول (المنصة)</strong></p>
            <p>.......................................</p>
        </div>
        <div style="text-align: center;">
            <p><strong>توقيع الطرف الثاني (المندوب)</strong></p>
            <p>.......................................</p>
        </div>
    </div>
</div>
HTML;

        DB::table('system_settings')->insertOrIgnore([
            'key' => 'courier_contract_template',
            'value' => $defaultContractTemplate,
            'group' => 'legal',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_settings')->where('key', 'courier_contract_template')->delete();
    }
};
