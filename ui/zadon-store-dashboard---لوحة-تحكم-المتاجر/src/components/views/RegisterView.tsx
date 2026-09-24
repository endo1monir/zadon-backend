import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { translations } from '../../utils/translations';
import { StoreCategory, TabKey } from '../../types';
import { 
  Store as StoreIcon, 
  ArrowLeft, 
  ArrowRight, 
  Building2, 
  MapPin, 
  Phone, 
  Mail, 
  FileText, 
  Clock, 
  Truck, 
  CheckCircle2, 
  Image as ImageIcon,
  User,
  Sparkles
} from 'lucide-react';

interface RegisterViewProps {
  onNavigate: (tab: TabKey) => void;
}

const LOGO_PRESETS = [
  { label: 'سوبرماركت', url: 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=160&auto=format&fit=crop&q=80' },
  { label: 'صيدلية', url: 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=160&auto=format&fit=crop&q=80' },
  { label: 'تمور ومحامص', url: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=160&auto=format&fit=crop&q=80' },
  { label: 'مخبوزات', url: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=160&auto=format&fit=crop&q=80' },
  { label: 'خضار وفواكه', url: 'https://images.unsplash.com/photo-1610348725531-843dff563e2c?w=160&auto=format&fit=crop&q=80' },
];

export const RegisterView: React.FC<RegisterViewProps> = ({ onNavigate }) => {
  const { registerStore, language } = useStore();
  const t = translations[language];
  const isRtl = language === 'ar';

  const [formData, setFormData] = useState({
    nameAr: '',
    nameEn: '',
    category: 'supermarket' as StoreCategory,
    addressAr: 'طريق الملك فهد، حي الملقا',
    addressEn: 'King Fahd Road, Al Malqa',
    city: 'الرياض',
    phone: '+966 5',
    email: '',
    crNumber: '',
    vatNumber: '',
    managerName: '',
    prepTimeMin: 15,
    deliveryFee: 12,
    minOrder: 35,
    isOpen24_7: false,
    openingTime: '08:00',
    closingTime: '23:30',
    deliveryRadiusKm: 10,
    logo: LOGO_PRESETS[0].url,
    coverImage: 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=1200&auto=format&fit=crop&q=80',
    status: 'open' as const,
  });

  const [isSubmitted, setIsSubmitted] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.nameAr.trim()) return;

    const fullCr = formData.crNumber.trim() || ('1010' + Math.floor(100000 + Math.random() * 900000));
    const fullVat = formData.vatNumber.trim() || ('310' + Math.floor(100000000 + Math.random() * 900000000) + '00003');
    const fullEmail = formData.email.trim() || `store-${Date.now()}@zadon.sa`;
    const fullManager = formData.managerName.trim() || (isRtl ? 'مدير المتجر' : 'Store Manager');

    registerStore({
      ...formData,
      crNumber: fullCr,
      vatNumber: fullVat,
      email: fullEmail,
      managerName: fullManager,
    });

    setIsSubmitted(true);
    setTimeout(() => {
      onNavigate('overview');
    }, 1200);
  };

  if (isSubmitted) {
    return (
      <div className="max-w-xl mx-auto py-16 px-4 text-center">
        <div className="w-16 h-16 rounded-full bg-[#ecfdf5] text-[#006948] flex items-center justify-center mx-auto mb-4 animate-bounce">
          <CheckCircle2 className="w-8 h-8" />
        </div>
        <h2 className="text-2xl font-black text-[#0b1c30] mb-2">
          {isRtl ? 'تم تسجيل المتجر بنجاح!' : 'Store Registered Successfully!'}
        </h2>
        <p className="text-sm text-gray-500">
          {isRtl 
            ? 'جاري نقلك مباشرة إلى لوحة التحكم لإدارة المنتجات والطلبات...'
            : 'Redirecting you to the dashboard to manage products and incoming orders...'}
        </p>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      
      {/* Top Header */}
      <div className="bg-white rounded-3xl border border-[#e2e8f0] p-6 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div className="flex items-center gap-3.5">
          <div className="w-12 h-12 rounded-2xl bg-[#006948] text-white flex items-center justify-center shadow-xs shrink-0">
            <StoreIcon className="w-6 h-6" />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <h1 className="text-xl sm:text-2xl font-black text-[#0b1c30] tracking-tight">
                {isRtl ? 'تسجيل متجر جديد' : 'Register New Store'}
              </h1>
              <span className="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#85f8c4]/40 text-[#005137]">
                {isRtl ? 'شريك جديد' : 'New Partner'}
              </span>
            </div>
            <p className="text-xs text-gray-500 mt-1">
              {isRtl 
                ? 'انضم إلى شبكة زادون للتوصيل الفوري وابدأ في استقبال طلبات العملاء'
                : 'Join Zadon rapid delivery merchant network and start receiving orders'}
            </p>
          </div>
        </div>

        <button
          type="button"
          onClick={() => onNavigate('overview')}
          className="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 text-xs font-bold transition-colors"
        >
          {isRtl ? <ArrowRight className="w-3.5 h-3.5" /> : <ArrowLeft className="w-3.5 h-3.5" />}
          <span>{isRtl ? 'العودة للرئيسية' : 'Back to Dashboard'}</span>
        </button>
      </div>

      {/* Registration Form */}
      <form onSubmit={handleSubmit} className="space-y-6">
        
        {/* Section 1: Basic Store Information */}
        <div className="bg-white rounded-3xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
          <div className="flex items-center gap-2 border-b border-gray-100 pb-3">
            <Building2 className="w-4 h-4 text-[#006948]" />
            <h3 className="font-extrabold text-sm text-[#0b1c30]">
              {isRtl ? 'معلومات المتجر والنشاط' : 'Store & Category Details'}
            </h3>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1.5">
                {isRtl ? 'اسم المتجر (بالعربية)' : 'Store Name (Arabic)'} <span className="text-red-500">*</span>
              </label>
              <input
                type="text"
                required
                value={formData.nameAr}
                onChange={e => setFormData({ ...formData, nameAr: e.target.value })}
                placeholder={isRtl ? 'مثال: أسواق النخبة المركزية' : 'e.g. Al Nokhba Central Market'}
                className="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-[#006948] focus:ring-1 focus:ring-[#006948] focus:outline-none transition-all"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1.5">
                {isRtl ? 'اسم المتجر (بالإنجليزية)' : 'Store Name (English)'}
              </label>
              <input
                type="text"
                dir="ltr"
                value={formData.nameEn}
                onChange={e => setFormData({ ...formData, nameEn: e.target.value })}
                placeholder="e.g. Elite Fresh Market"
                className="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-[#006948] focus:ring-1 focus:ring-[#006948] focus:outline-none text-left transition-all"
              />
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1.5">
                {isRtl ? 'تصنيف النشاط' : 'Business Category'} <span className="text-red-500">*</span>
              </label>
              <select
                value={formData.category}
                onChange={e => setFormData({ ...formData, category: e.target.value as StoreCategory })}
                className="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-[#006948] focus:ring-1 focus:ring-[#006948] focus:outline-none bg-white font-medium"
              >
                <option value="supermarket">{isRtl ? 'سوبرماركت وبقالة شاملة' : 'Supermarket & Groceries'}</option>
                <option value="pharmacy">{isRtl ? 'صيدلية ومستلزمات طبية' : 'Pharmacy & Wellness'}</option>
                <option value="dates">{isRtl ? 'تمور ومحامص وقهوة' : 'Dates, Roastery & Coffee'}</option>
                <option value="bakery">{isRtl ? 'مخبوزات وحلويات' : 'Bakery & Sweets'}</option>
                <option value="produce">{isRtl ? 'خضار وفواكه طازجة' : 'Fresh Produce'}</option>
              </select>
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1.5">
                {isRtl ? 'المدينة' : 'City'}
              </label>
              <select
                value={formData.city}
                onChange={e => setFormData({ ...formData, city: e.target.value })}
                className="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-[#006948] focus:ring-1 focus:ring-[#006948] focus:outline-none bg-white font-medium"
              >
                <option value="الرياض">الرياض (Riyadh)</option>
                <option value="جدة">جدة (Jeddah)</option>
                <option value="الدمام">الدمام (Dammam)</option>
                <option value="مكة المكرمة">مكة المكرمة (Makkah)</option>
                <option value="المدينة المنورة">المدينة المنورة (Madinah)</option>
                <option value="الخبر">الخبر (Khobar)</option>
              </select>
            </div>
          </div>
        </div>

        {/* Section 2: Location & Operations */}
        <div className="bg-white rounded-3xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
          <div className="flex items-center gap-2 border-b border-gray-100 pb-3">
            <MapPin className="w-4 h-4 text-[#006948]" />
            <h3 className="font-extrabold text-sm text-[#0b1c30]">
              {isRtl ? 'الموقع ونطاق التوصيل' : 'Location & Delivery Parameters'}
            </h3>
          </div>

          <div>
            <label className="block text-xs font-bold text-gray-700 mb-1.5">
              {isRtl ? 'عنوان الفرع بالتفصيل' : 'Detailed Branch Address'}
            </label>
            <input
              type="text"
              value={formData.addressAr}
              onChange={e => setFormData({ ...formData, addressAr: e.target.value })}
              placeholder={isRtl ? 'مثال: طريق الملك فهد، تقاطع أنس بن مالك، حي الملقا' : 'e.g. King Fahd Road, Al Malqa'}
              className="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-[#006948] focus:ring-1 focus:ring-[#006948] focus:outline-none transition-all"
            />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1.5">
                {isRtl ? 'مدة التجهيز التقديرية (دقيقة)' : 'Prep Time (minutes)'}
              </label>
              <input
                type="number"
                min="5"
                max="90"
                value={formData.prepTimeMin}
                onChange={e => setFormData({ ...formData, prepTimeMin: Number(e.target.value) })}
                className="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-[#006948] focus:outline-none"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1.5">
                {isRtl ? 'الحد الأدنى للطلب (ر.س)' : 'Minimum Order (SAR)'}
              </label>
              <input
                type="number"
                min="0"
                value={formData.minOrder}
                onChange={e => setFormData({ ...formData, minOrder: Number(e.target.value) })}
                className="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-[#006948] focus:outline-none"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1.5">
                {isRtl ? 'نطاق التغطية (كم)' : 'Delivery Radius (km)'}
              </label>
              <input
                type="number"
                min="1"
                max="50"
                value={formData.deliveryRadiusKm}
                onChange={e => setFormData({ ...formData, deliveryRadiusKm: Number(e.target.value) })}
                className="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-[#006948] focus:outline-none"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1.5">
                {isRtl ? 'سعر التوصيل (ر.س)' : 'Delivery Fee (SAR)'}
              </label>
              <input
                type="number"
                min="0"
                step="0.5"
                value={formData.deliveryFee}
                onChange={e => setFormData({ ...formData, deliveryFee: Number(e.target.value) })}
                className="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-[#006948] focus:outline-none"
              />
            </div>
          </div>

          <div className="pt-2">
            <label className="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                checked={formData.isOpen24_7}
                onChange={e => setFormData({ ...formData, isOpen24_7: e.target.checked })}
                className="rounded text-[#006948] focus:ring-[#006948] w-4 h-4"
              />
              <span className="text-xs font-bold text-gray-700">
                {isRtl ? 'يعمل الفرع 24 ساعة طوال أيام الأسبوع' : 'Open 24/7 all week'}
              </span>
            </label>
          </div>
        </div>

        {/* Section 3: Official Verification & Contact */}
        <div className="bg-white rounded-3xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
          <div className="flex items-center gap-2 border-b border-gray-100 pb-3">
            <FileText className="w-4 h-4 text-[#006948]" />
            <h3 className="font-extrabold text-sm text-[#0b1c30]">
              {isRtl ? 'الوثائق النظامية وبيانات التواصل' : 'Legal Documents & Contact'}
            </h3>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1.5">
                {isRtl ? 'رقم السجل التجاري (CR)' : 'Commercial Registration (CR)'}
              </label>
              <input
                type="text"
                dir="ltr"
                placeholder="1010XXXXXX"
                value={formData.crNumber}
                onChange={e => setFormData({ ...formData, crNumber: e.target.value })}
                className="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-[#006948] focus:outline-none font-mono text-left"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1.5">
                {isRtl ? 'الرقم الضريبي (15 رقم)' : 'VAT Number (15 digits)'}
              </label>
              <input
                type="text"
                dir="ltr"
                placeholder="310XXXXXXXX0003"
                value={formData.vatNumber}
                onChange={e => setFormData({ ...formData, vatNumber: e.target.value })}
                className="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-[#006948] focus:outline-none font-mono text-left"
              />
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1.5">
                {isRtl ? 'رقم الجوال' : 'Phone Number'} <span className="text-red-500">*</span>
              </label>
              <input
                type="tel"
                dir="ltr"
                required
                value={formData.phone}
                onChange={e => setFormData({ ...formData, phone: e.target.value })}
                className="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-[#006948] focus:outline-none font-mono text-left"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1.5">
                {isRtl ? 'البريد الإلكتروني' : 'Official Email'}
              </label>
              <input
                type="email"
                dir="ltr"
                placeholder="store@domain.sa"
                value={formData.email}
                onChange={e => setFormData({ ...formData, email: e.target.value })}
                className="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-[#006948] focus:outline-none text-left"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1.5">
                {isRtl ? 'اسم المشرف / المدير' : 'Manager Name'}
              </label>
              <input
                type="text"
                placeholder={isRtl ? 'مثال: أحمد السعيد' : 'e.g. Ahmed Al-Saeed'}
                value={formData.managerName}
                onChange={e => setFormData({ ...formData, managerName: e.target.value })}
                className="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:border-[#006948] focus:outline-none"
              />
            </div>
          </div>
        </div>

        {/* Section 4: Logo Selection */}
        <div className="bg-white rounded-3xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
          <div className="flex items-center gap-2 border-b border-gray-100 pb-3">
            <ImageIcon className="w-4 h-4 text-[#006948]" />
            <h3 className="font-extrabold text-sm text-[#0b1c30]">
              {isRtl ? 'شعار وهوية المتجر' : 'Store Logo & Identity'}
            </h3>
          </div>

          <div>
            <label className="block text-xs font-bold text-gray-700 mb-2">
              {isRtl ? 'اختر أيقونة مناسبة لنشاطك:' : 'Select a preset visual logo:'}
            </label>
            <div className="flex flex-wrap gap-3">
              {LOGO_PRESETS.map((preset, i) => (
                <button
                  type="button"
                  key={i}
                  onClick={() => setFormData({ ...formData, logo: preset.url })}
                  className={`flex items-center gap-2 p-2 rounded-2xl border transition-all ${
                    formData.logo === preset.url
                      ? 'border-[#006948] bg-[#ecfdf5] shadow-xs'
                      : 'border-gray-200 hover:border-gray-300'
                  }`}
                >
                  <img src={preset.url} alt={preset.label} className="w-10 h-10 rounded-xl object-cover" />
                  <span className="text-xs font-bold text-gray-700">{preset.label}</span>
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* Submit Actions */}
        <div className="flex items-center justify-end gap-3 pt-2">
          <button
            type="button"
            onClick={() => onNavigate('overview')}
            className="px-5 py-3 rounded-2xl border border-gray-300 text-gray-700 text-xs font-bold hover:bg-gray-50 transition-colors"
          >
            {t.cancel}
          </button>
          <button
            type="submit"
            className="px-8 py-3 rounded-2xl bg-[#006948] hover:bg-[#00855d] text-white text-xs font-bold shadow-md active:scale-95 transition-all flex items-center gap-2"
          >
            <Sparkles className="w-4 h-4 text-[#85f8c4]" />
            <span>{isRtl ? 'تسجيل المتجر وتفعيل الحساب' : 'Register Store & Activate Account'}</span>
          </button>
        </div>

      </form>

    </div>
  );
};
