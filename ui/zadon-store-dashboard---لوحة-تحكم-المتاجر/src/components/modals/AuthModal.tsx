import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { translations } from '../../utils/translations';
import { StoreCategory } from '../../types';
import { 
  Store as StoreIcon, 
  PlusCircle, 
  Building, 
  MapPin, 
  Phone, 
  Clock,
  Sparkles,
  X,
  Lock,
  Eye,
  EyeOff,
  LogIn,
  AlertCircle,
  ArrowRight,
  ArrowLeft
} from 'lucide-react';

interface AuthModalProps {
  isOpen: boolean;
  onClose: () => void;
  initialMode?: 'login' | 'register';
  onNavigateToRegister?: () => void;
}

export const AuthModal: React.FC<AuthModalProps> = ({ 
  isOpen, 
  onClose, 
  initialMode = 'login',
  onNavigateToRegister
}) => {
  const { loginWithPhoneAndPassword, registerStore, language } = useStore();
  const t = translations[language];

  const [mode, setMode] = useState<'login' | 'register'>(initialMode);
  
  // Phone & Password Login Form State
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [rememberMe, setRememberMe] = useState(true);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [showForgotNotice, setShowForgotNotice] = useState(false);

  const handleSwitchToRegister = () => {
    if (onNavigateToRegister) {
      onClose();
      onNavigateToRegister();
    } else {
      setMode('register');
    }
  };

  const handleCredentialLogin = (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg(null);
    setIsSubmitting(true);

    setTimeout(() => {
      const res = loginWithPhoneAndPassword(phone, password);
      setIsSubmitting(false);

      if (res.success) {
        onClose();
      } else {
        setErrorMsg(res.error || (language === 'ar' ? 'بيانات الدخول غير صحيحة' : 'Invalid login credentials'));
      }
    }, 300);
  };

  // New store form state
  const [newStoreData, setNewStoreData] = useState({
    nameAr: '',
    nameEn: '',
    category: 'supermarket' as StoreCategory,
    addressAr: 'حي العليا، الرياض',
    addressEn: 'Al Olaya, Riyadh',
    city: 'الرياض',
    phone: '+966 50 123 4567',
    email: 'store@zadon.sa',
    crNumber: '1010' + Math.floor(100000 + Math.random() * 900000),
    vatNumber: '310' + Math.floor(100000000 + Math.random() * 900000000) + '00003',
    managerName: 'أحمد السعيد',
    prepTimeMin: 15,
    deliveryFee: 12,
    minOrder: 30,
    isOpen24_7: false,
    openingTime: '08:00',
    closingTime: '23:30',
    deliveryRadiusKm: 10,
    logo: 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=160&auto=format&fit=crop&q=80',
    coverImage: 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=1200&auto=format&fit=crop&q=80',
    status: 'open' as const
  });

  if (!isOpen) return null;

  const handleRegisterSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newStoreData.nameAr.trim()) return;
    registerStore(newStoreData);
    onClose();
  };

  return (
    <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in duration-200">
      <div className="bg-white rounded-3xl w-full max-w-xl max-h-[92vh] overflow-y-auto shadow-2xl border border-gray-100 flex flex-col relative">
        
        {/* Close Button */}
        <button
          onClick={onClose}
          className="absolute top-4 left-4 w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 z-10 transition-colors"
        >
          <X className="w-4 h-4" />
        </button>

        {/* Modal Top Banner */}
        <div className="p-6 bg-gradient-to-l from-[#006948] to-[#00855d] text-white rounded-t-3xl">
          <div className="flex items-center gap-2 mb-2">
            <div className="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
              <StoreIcon className="w-4 h-4 text-[#85f8c4]" />
            </div>
            <span className="text-xs font-bold text-[#85f8c4]">{t.appName}</span>
          </div>
          <h2 className="text-xl font-black tracking-tight">
            {mode === 'login' 
              ? (language === 'ar' ? 'تسجيل الدخول للمتجر' : 'Store Login')
              : (language === 'ar' ? 'تسجيل متجر جديد في زادون' : 'Register New Store')}
          </h2>
          <p className="text-xs text-white/80 mt-1">
            {mode === 'login' 
              ? (language === 'ar' 
                  ? 'أدخل رقم الجوال وكلمة المرور المسجلة للمتجر للدخول إلى لوحة التحكم' 
                  : 'Enter your registered phone number and password to access the store')
              : (language === 'ar' 
                  ? 'سجل متجرك الآن للظهور في التطبيق وتفعيل التوصيل السريع خلال 15-25 دقيقة' 
                  : 'Register your store now to activate quick delivery in 15-25 minutes')}
          </p>

          {/* Mode Switch Tabs */}
          <div className="flex items-center p-1 bg-black/20 rounded-xl mt-4 gap-1">
            <button
              onClick={() => {
                setMode('login');
                setErrorMsg(null);
              }}
              className={`flex-1 py-1.5 rounded-lg text-xs font-bold transition-all ${
                mode === 'login' ? 'bg-white text-[#006948] shadow-xs' : 'text-white/80 hover:text-white'
              }`}
            >
              {language === 'ar' ? 'تسجيل الدخول' : 'Log In'}
            </button>
            <button
              onClick={handleSwitchToRegister}
              className={`flex-1 py-1.5 rounded-lg text-xs font-bold transition-all ${
                mode === 'register' ? 'bg-white text-[#006948] shadow-xs' : 'text-white/80 hover:text-white'
              }`}
            >
              {language === 'ar' ? 'تسجيل متجر جديد' : 'Register'}
            </button>
          </div>
        </div>

        {/* Body Content */}
        <div className="p-6">
          {mode === 'login' ? (
            <div className="space-y-5">
              
              {/* Phone & Password Form - Sole Login Method */}
              <form onSubmit={handleCredentialLogin} className="space-y-4">
                
                {/* Error Notification */}
                {errorMsg && (
                  <div className="p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2 animate-in fade-in">
                    <AlertCircle className="w-4 h-4 shrink-0 text-red-600" />
                    <span>{errorMsg}</span>
                  </div>
                )}

                {/* Phone Input */}
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1.5">
                    {language === 'ar' ? 'رقم الجوال المسجل' : 'Registered Phone Number'} <span className="text-red-500">*</span>
                  </label>
                  <div className="relative flex items-center">
                    <div className="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none text-gray-400">
                      <Phone className="w-4 h-4" />
                    </div>
                    <div className="absolute inset-y-0 end-0 flex items-center pe-3 pointer-events-none text-xs font-bold text-gray-400">
                      🇸🇦 +966
                    </div>
                    <input
                      type="tel"
                      dir="ltr"
                      required
                      value={phone}
                      onChange={e => {
                        setPhone(e.target.value);
                        if (errorMsg) setErrorMsg(null);
                      }}
                      placeholder="05X XXX XXXX"
                      className="w-full text-sm ps-9 pe-20 py-2.5 rounded-xl border border-gray-300 focus:border-[#006948] focus:ring-1 focus:ring-[#006948] focus:outline-none font-mono text-left transition-all"
                    />
                  </div>
                  <p className="text-[11px] text-gray-400 mt-1">
                    {language === 'ar' ? 'أدخل رقم جوال مدير المتجر المسجل' : 'Enter registered store manager phone number'}
                  </p>
                </div>

                {/* Password Input */}
                <div>
                  <div className="flex items-center justify-between mb-1.5">
                    <label className="text-xs font-bold text-gray-700">
                      {language === 'ar' ? 'كلمة المرور' : 'Password'} <span className="text-red-500">*</span>
                    </label>
                    <button
                      type="button"
                      onClick={() => setShowForgotNotice(!showForgotNotice)}
                      className="text-[11px] text-[#006948] font-bold hover:underline"
                    >
                      {language === 'ar' ? 'نسيت كلمة المرور؟' : 'Forgot password?'}
                    </button>
                  </div>

                  <div className="relative flex items-center">
                    <div className="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none text-gray-400">
                      <Lock className="w-4 h-4" />
                    </div>
                    <input
                      type={showPassword ? 'text' : 'password'}
                      dir="ltr"
                      required
                      value={password}
                      onChange={e => {
                        setPassword(e.target.value);
                        if (errorMsg) setErrorMsg(null);
                      }}
                      placeholder="••••••••"
                      className="w-full text-sm ps-9 pe-10 py-2.5 rounded-xl border border-gray-300 focus:border-[#006948] focus:ring-1 focus:ring-[#006948] focus:outline-none font-mono text-left transition-all"
                    />
                    <button
                      type="button"
                      onClick={() => setShowPassword(!showPassword)}
                      className="absolute inset-y-0 end-0 flex items-center pe-3 text-gray-400 hover:text-gray-600 transition-colors"
                      title={showPassword ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور'}
                    >
                      {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                    </button>
                  </div>
                </div>

                {/* Forgot Notice Box */}
                {showForgotNotice && (
                  <div className="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-[#005137] space-y-1 animate-in fade-in">
                    <p className="font-bold">
                      {language === 'ar' ? 'استعادة بيانات الدخول' : 'Account Recovery'}
                    </p>
                    <p className="text-[11px] leading-relaxed opacity-90">
                      {language === 'ar'
                        ? 'في حال نسيت كلمة المرور الخاصة بمتجرك، يرجى التواصل مع الدعم الفني لشركاء زادون عبر الرقم الموحد 920000000 أو البريد partners@zadon.sa لإعادة التعيين.'
                        : 'If you forgot your password, please contact Zadon partner support at partners@zadon.sa or 920000000 to reset your credentials.'}
                    </p>
                  </div>
                )}

                {/* Remember Me Toggle */}
                <div className="flex items-center justify-between pt-1">
                  <label className="flex items-center gap-2 cursor-pointer text-xs text-gray-600 select-none">
                    <input
                      type="checkbox"
                      checked={rememberMe}
                      onChange={e => setRememberMe(e.target.checked)}
                      className="rounded text-[#006948] focus:ring-[#006948]"
                    />
                    <span>{language === 'ar' ? 'تذكر بيانات تسجيل الدخول' : 'Remember me on this device'}</span>
                  </label>
                </div>

                {/* Submit Button */}
                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="w-full py-3 rounded-xl bg-[#006948] hover:bg-[#00855d] text-white text-xs sm:text-sm font-bold shadow-xs active:scale-95 transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                >
                  {isSubmitting ? (
                    <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                  ) : (
                    <>
                      <LogIn className="w-4 h-4" />
                      <span>{language === 'ar' ? 'تسجيل الدخول' : 'Log In'}</span>
                    </>
                  )}
                </button>

              </form>

              {/* Bottom Register Prompt */}
              <div className="pt-3 text-center border-t border-gray-100">
                <button
                  type="button"
                  onClick={handleSwitchToRegister}
                  className="text-xs text-[#006948] font-bold hover:underline inline-flex items-center gap-1.5"
                >
                  <PlusCircle className="w-3.5 h-3.5" />
                  <span>
                    {language === 'ar' 
                      ? 'ليس لديك حساب متجر؟ تسجيل متجر جديد في زادون' 
                      : "Don't have a store account? Register a new store"}
                  </span>
                </button>
              </div>
            </div>
          ) : (
            /* Register Form View */
            <form onSubmit={handleRegisterSubmit} className="space-y-4">
              
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    اسم المتجر (بالعربية) <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="مثال: أسواق النخبة المركزية"
                    value={newStoreData.nameAr}
                    onChange={e => setNewStoreData({ ...newStoreData, nameAr: e.target.value })}
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
                    placeholder="e.g. Elite Central Market"
                    value={newStoreData.nameEn}
                    onChange={e => setNewStoreData({ ...newStoreData, nameEn: e.target.value })}
                    className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none text-left"
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    تصنيف المتجر <span className="text-red-500">*</span>
                  </label>
                  <select
                    value={newStoreData.category}
                    onChange={e => setNewStoreData({ ...newStoreData, category: e.target.value as StoreCategory })}
                    className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none bg-white font-medium"
                  >
                    <option value="supermarket">سوبرماركت وبقالة شاملة</option>
                    <option value="pharmacy">صيدلية ومستلزمات طبية</option>
                    <option value="dates">تمور ومحامص وقهوة</option>
                    <option value="bakery">مخبوزات وحلويات</option>
                    <option value="produce">خضار وفواكه طازجة</option>
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    المدينة
                  </label>
                  <select
                    value={newStoreData.city}
                    onChange={e => setNewStoreData({ ...newStoreData, city: e.target.value })}
                    className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none bg-white font-medium"
                  >
                    <option value="الرياض">الرياض</option>
                    <option value="جدة">جدة</option>
                    <option value="الدمام">الدمام</option>
                    <option value="مكة المكرمة">مكة المكرمة</option>
                    <option value="المدينة المنورة">المدينة المنورة</option>
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    رقم السجل التجاري (CR)
                  </label>
                  <input
                    type="text"
                    dir="ltr"
                    value={newStoreData.crNumber}
                    onChange={e => setNewStoreData({ ...newStoreData, crNumber: e.target.value })}
                    className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none font-mono text-left"
                  />
                </div>

                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    الرقم الضريبي (15 رقم)
                  </label>
                  <input
                    type="text"
                    dir="ltr"
                    value={newStoreData.vatNumber}
                    onChange={e => setNewStoreData({ ...newStoreData, vatNumber: e.target.value })}
                    className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none font-mono text-left"
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    رقم جوال المتجر <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="tel"
                    dir="ltr"
                    required
                    value={newStoreData.phone}
                    onChange={e => setNewStoreData({ ...newStoreData, phone: e.target.value })}
                    className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none text-left font-mono"
                  />
                </div>

                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    اسم مدير المتجر / المشرف
                  </label>
                  <input
                    type="text"
                    value={newStoreData.managerName}
                    onChange={e => setNewStoreData({ ...newStoreData, managerName: e.target.value })}
                    className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">
                  عنوان الفرع بالتفصيل
                </label>
                <input
                  type="text"
                  value={newStoreData.addressAr}
                  onChange={e => setNewStoreData({ ...newStoreData, addressAr: e.target.value })}
                  placeholder="مثال: طريق الملك فهد، حي الملقا"
                  className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none"
                />
              </div>

              <div className="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                <button
                  type="button"
                  onClick={onClose}
                  className="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-xs font-bold hover:bg-gray-50"
                >
                  {t.cancel}
                </button>
                <button
                  type="submit"
                  className="px-6 py-2.5 rounded-xl bg-[#006948] hover:bg-[#00855d] text-white text-xs font-bold shadow-xs active:scale-95 transition-all"
                >
                  إتمام التسجيل والدخول للمتجر
                </button>
              </div>

            </form>
          )}
        </div>

      </div>
    </div>
  );
};
