import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { translations } from '../../utils/translations';
import { StoreCategory, StoreStatus } from '../../types';
import { 
  Store, 
  MapPin, 
  Phone, 
  Mail, 
  FileText, 
  Clock, 
  Save, 
  CheckCircle2, 
  Building,
  DollarSign,
  Truck
} from 'lucide-react';

export const StoreProfileView: React.FC = () => {
  const { currentStore, updateStoreProfile, language } = useStore();
  const t = translations[language];

  const [savedSuccess, setSavedSuccess] = useState(false);

  const [formData, setFormData] = useState({
    nameAr: currentStore?.nameAr || '',
    nameEn: currentStore?.nameEn || '',
    category: currentStore?.category || 'pharmacy',
    addressAr: currentStore?.addressAr || '',
    addressEn: currentStore?.addressEn || '',
    city: currentStore?.city || 'الرياض',
    phone: currentStore?.phone || '',
    email: currentStore?.email || '',
    crNumber: currentStore?.crNumber || '',
    vatNumber: currentStore?.vatNumber || '',
    managerName: currentStore?.managerName || '',
    prepTimeMin: currentStore?.prepTimeMin || 15,
    minOrder: currentStore?.minOrder || 20,
    deliveryFee: currentStore?.deliveryFee || 12,
    deliveryRadiusKm: currentStore?.deliveryRadiusKm || 10,
    isOpen24_7: currentStore?.isOpen24_7 || false,
    openingTime: currentStore?.openingTime || '08:00',
    closingTime: currentStore?.closingTime || '23:30',
    status: currentStore?.status || 'open'
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    updateStoreProfile(formData);
    setSavedSuccess(true);
    setTimeout(() => setSavedSuccess(false), 3000);
  };

  if (!currentStore) return null;

  return (
    <div className="space-y-6 max-w-4xl">
      
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-xl sm:text-2xl font-black text-[#0b1c30] tracking-tight flex items-center gap-2">
            <Store className="w-6 h-6 text-[#006948]" />
            <span>{t.storeProfile}</span>
          </h2>
          <p className="text-xs text-gray-500 mt-1">
            {language === 'ar' ? 'إدارة بيانات المتجر، رخصة العمل، السجل التجاري وأوقات التوصيل' : 'Manage store identity, registration, and delivery configurations'}
          </p>
        </div>

        {savedSuccess && (
          <div className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#ecfdf5] text-[#006948] text-xs font-bold animate-in fade-in duration-200">
            <CheckCircle2 className="w-4 h-4" />
            <span>{t.saveChanges} بنجاح!</span>
          </div>
        )}
      </div>

      {/* Main Profile Form */}
      <form onSubmit={handleSubmit} className="space-y-6">
        
        {/* Identity & Visual Branding Card */}
        <div className="bg-white rounded-3xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
          <h3 className="font-extrabold text-sm text-[#0b1c30] flex items-center gap-2 pb-3 border-b border-gray-100">
            <Building className="w-4 h-4 text-[#006948]" />
            <span>هوية المتجر والعلامة التجارية</span>
          </h3>

          <div className="flex flex-col sm:flex-row items-center gap-4 pb-2">
            <img 
              src={currentStore.logo} 
              alt={currentStore.nameAr}
              className="w-20 h-20 rounded-2xl object-cover border-2 border-gray-100 shadow-xs"
            />
            <div className="text-center sm:text-right">
              <span className="text-xs font-bold text-gray-700 block">شعار المتجر الرسمي</span>
              <p className="text-[11px] text-gray-400 mt-0.5">يظهر الشعار في تطبيق زادون للعملاء وعلى الفواتير الضريبية</p>
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1">
                اسم المتجر (بالعربية) <span className="text-red-500">*</span>
              </label>
              <input
                type="text"
                required
                value={formData.nameAr}
                onChange={e => setFormData({ ...formData, nameAr: e.target.value })}
                className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1">
                اسم المتجر (بالإنجليزية)
              </label>
              <input
                type="text"
                dir="ltr"
                value={formData.nameEn}
                onChange={e => setFormData({ ...formData, nameEn: e.target.value })}
                className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none text-left"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1">
                تصنيف المتجر في التطبيق
              </label>
              <select
                value={formData.category}
                onChange={e => setFormData({ ...formData, category: e.target.value as StoreCategory })}
                className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none bg-white"
              >
                <option value="pharmacy">صيدلية وعناية طبية (Pharmacy)</option>
                <option value="supermarket">سوبرماركت وبقالة (Supermarket)</option>
                <option value="dates">تمور ومحامص وقهوة (Dates & Roastery)</option>
                <option value="bakery">مخابز وحلويات (Bakery)</option>
                <option value="produce">خضار وفواكه طازجة (Fresh Produce)</option>
              </select>
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1">
                {t.managerName}
              </label>
              <input
                type="text"
                value={formData.managerName}
                onChange={e => setFormData({ ...formData, managerName: e.target.value })}
                className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none"
              />
            </div>
          </div>
        </div>

        {/* Commercial & Legal Registration */}
        <div className="bg-white rounded-3xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
          <h3 className="font-extrabold text-sm text-[#0b1c30] flex items-center gap-2 pb-3 border-b border-gray-100">
            <FileText className="w-4 h-4 text-[#006948]" />
            <span>البيانات الضريبية والتجارية (المملكة العربية السعودية)</span>
          </h3>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1">
                {t.crNumber} (10 أرقام)
              </label>
              <input
                type="text"
                dir="ltr"
                value={formData.crNumber}
                onChange={e => setFormData({ ...formData, crNumber: e.target.value })}
                className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none font-mono text-left"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1">
                {t.vatNumber} (15 رقماً لهيئة الزكاة والضريبة)
              </label>
              <input
                type="text"
                dir="ltr"
                value={formData.vatNumber}
                onChange={e => setFormData({ ...formData, vatNumber: e.target.value })}
                className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none font-mono text-left"
              />
            </div>
          </div>
        </div>

        {/* Location & Contact */}
        <div className="bg-white rounded-3xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
          <h3 className="font-extrabold text-sm text-[#0b1c30] flex items-center gap-2 pb-3 border-b border-gray-100">
            <MapPin className="w-4 h-4 text-[#006948]" />
            <span>الموقع الجغرافي ومعلومات التواصل</span>
          </h3>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1">
                {t.storeCity}
              </label>
              <select
                value={formData.city}
                onChange={e => setFormData({ ...formData, city: e.target.value })}
                className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none bg-white"
              >
                <option value="الرياض">الرياض</option>
                <option value="جدة">جدة</option>
                <option value="الدمام">الدمام</option>
                <option value="مكة المكرمة">مكة المكرمة</option>
                <option value="المدينة المنورة">المدينة المنورة</option>
                <option value="الخبر">الخبر</option>
              </select>
            </div>

            <div className="sm:col-span-2">
              <label className="block text-xs font-bold text-gray-700 mb-1">
                عنوان الفرع بالتفصيل
              </label>
              <input
                type="text"
                value={formData.addressAr}
                onChange={e => setFormData({ ...formData, addressAr: e.target.value })}
                placeholder="مثال: شارع التحلية، حي العليا"
                className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1">
                {t.storePhone}
              </label>
              <input
                type="tel"
                dir="ltr"
                value={formData.phone}
                onChange={e => setFormData({ ...formData, phone: e.target.value })}
                placeholder="+966 5X XXX XXXX"
                className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none text-left"
              />
            </div>

            <div className="sm:col-span-2">
              <label className="block text-xs font-bold text-gray-700 mb-1">
                {t.storeEmail}
              </label>
              <input
                type="email"
                dir="ltr"
                value={formData.email}
                onChange={e => setFormData({ ...formData, email: e.target.value })}
                className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none text-left"
              />
            </div>
          </div>
        </div>

        {/* Operating Hours & Dispatch Settings */}
        <div className="bg-white rounded-3xl border border-[#e2e8f0] p-6 shadow-xs space-y-4">
          <h3 className="font-extrabold text-sm text-[#0b1c30] flex items-center gap-2 pb-3 border-b border-gray-100">
            <Clock className="w-4 h-4 text-[#006948]" />
            <span>إعدادات التشغيل وزمن التجهيز الفوري</span>
          </h3>

          <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1">
                {t.prepTime} ({t.mins})
              </label>
              <input
                type="number"
                min="5"
                max="60"
                value={formData.prepTimeMin}
                onChange={e => setFormData({ ...formData, prepTimeMin: parseInt(e.target.value) || 15 })}
                className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none font-bold"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1">
                {t.minOrder} ({t.sar})
              </label>
              <input
                type="number"
                min="0"
                value={formData.minOrder}
                onChange={e => setFormData({ ...formData, minOrder: parseFloat(e.target.value) || 0 })}
                className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none font-bold"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1">
                {t.deliveryFee} ({t.sar})
              </label>
              <input
                type="number"
                min="0"
                value={formData.deliveryFee}
                onChange={e => setFormData({ ...formData, deliveryFee: parseFloat(e.target.value) || 0 })}
                className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none font-bold"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1">
                {t.deliveryRadius}
              </label>
              <input
                type="number"
                min="1"
                max="50"
                value={formData.deliveryRadiusKm}
                onChange={e => setFormData({ ...formData, deliveryRadiusKm: parseFloat(e.target.value) || 10 })}
                className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none font-bold"
              />
            </div>
          </div>

          <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-2xl border border-gray-200">
            <input
              type="checkbox"
              id="open247Toggle"
              checked={formData.isOpen24_7}
              onChange={e => setFormData({ ...formData, isOpen24_7: e.target.checked })}
              className="w-4 h-4 rounded text-[#006948] focus:ring-[#006948]"
            />
            <label htmlFor="open247Toggle" className="text-xs font-bold text-gray-800 cursor-pointer">
              {t.open24_7} (استقبال وتجهيز الطلبات على مدار الساعة مثل الصيدليات 24/7)
            </label>
          </div>
        </div>

        {/* Save Button */}
        <div className="flex justify-end pt-2">
          <button
            type="submit"
            className="w-full sm:w-auto px-8 py-3 rounded-xl bg-[#006948] hover:bg-[#00855d] text-white font-black text-sm shadow-md active:scale-95 transition-all flex items-center justify-center gap-2"
          >
            <Save className="w-4 h-4" />
            <span>{t.saveChanges}</span>
          </button>
        </div>

      </form>

    </div>
  );
};
