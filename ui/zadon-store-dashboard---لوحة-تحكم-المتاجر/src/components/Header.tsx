import React, { useState } from 'react';
import { useStore } from '../context/StoreContext';
import { translations } from '../utils/translations';
import { StoreStatus, TabKey } from '../types';
import { 
  Store as StoreIcon, 
  Bell, 
  Languages, 
  ChevronDown, 
  Check, 
  Clock,
  ShieldCheck,
  ArrowLeft,
  ArrowRight
} from 'lucide-react';

interface HeaderProps {
  onOpenAuth?: (mode: 'login' | 'register') => void;
  onOpenLoginModal?: () => void;
  onOpenRegisterModal?: () => void;
  onNavigate?: (tab: TabKey) => void;
  onOpenNotifications?: () => void;
}

export const Header: React.FC<HeaderProps> = ({ 
  onOpenAuth,
  onOpenLoginModal,
  onOpenRegisterModal,
  onNavigate,
  onOpenNotifications 
}) => {
  const { 
    currentStore, 
    setStoreStatus, 
    language, 
    toggleLanguage,
    newOrdersCount,
    unreadNotificationsCount,
    notifications
  } = useStore();

  const [isStatusMenuOpen, setIsStatusMenuOpen] = useState(false);
  const [isNotifDropdownOpen, setIsNotifDropdownOpen] = useState(false);

  const t = translations[language];
  const isRtl = language === 'ar';

  const handleAuth = (mode: 'login' | 'register') => {
    if (mode === 'register') {
      if (onNavigate) {
        onNavigate('register');
      } else if (onOpenRegisterModal) {
        onOpenRegisterModal();
      }
    } else {
      if (onOpenAuth) {
        onOpenAuth(mode);
      } else if (onOpenLoginModal) {
        onOpenLoginModal();
      }
    }
  };

  const handleOpenNotificationPage = () => {
    setIsNotifDropdownOpen(false);
    if (onOpenNotifications) {
      onOpenNotifications();
    } else if (onNavigate) {
      onNavigate('notifications');
    }
  };

  const statusColors: Record<StoreStatus, { bg: string; text: string; dot: string; label: string }> = {
    open: { bg: 'bg-[#ecfdf5]', text: 'text-[#006948]', dot: 'bg-[#006948]', label: t.statusOpen },
    busy: { bg: 'bg-[#fffbeb]', text: 'text-[#b45309]', dot: 'bg-[#f59e0b]', label: t.statusBusy },
    closed: { bg: 'bg-[#fef2f2]', text: 'text-[#b91c1c]', dot: 'bg-[#ef4444]', label: t.statusClosed },
  };

  const currentStatusConfig = currentStore ? statusColors[currentStore.status] : statusColors.open;

  return (
    <header className="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-[#e2e8f0] shadow-xs">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16 gap-3">
          
          {/* Brand & Store Selector */}
          <div className="flex items-center gap-3 min-w-0">
            {/* Zadon Brand Logo Icon */}
            <div className="flex items-center gap-2">
              <div className="w-10 h-10 rounded-xl bg-[#006948] text-white flex items-center justify-center font-bold shadow-xs">
                <StoreIcon className="w-5 h-5" />
              </div>
              <div className="hidden sm:flex flex-col">
                <div className="flex items-center gap-1.5">
                  <span className="font-extrabold text-lg text-[#006948] tracking-tight">زادون</span>
                  <span className="text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#85f8c4]/40 text-[#005137]">
                    بوابة المتاجر
                  </span>
                </div>
                <span className="text-[11px] text-gray-500 -mt-1 font-medium">{t.appSubtitle}</span>
              </div>
            </div>

            {/* Current Store Pill */}
            {currentStore ? (
              <div className="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#f8f9ff] border border-[#dce9ff] text-right">
                <img 
                  src={currentStore.logo} 
                  alt={currentStore.nameAr}
                  className="w-6 h-6 rounded-lg object-cover border border-gray-200"
                />
                <div className="flex flex-col min-w-0 max-w-[140px] sm:max-w-[200px]">
                  <span className="font-bold text-xs sm:text-sm text-[#0b1c30] truncate">
                    {language === 'ar' ? currentStore.nameAr : currentStore.nameEn}
                  </span>
                  <span className="text-[10px] text-gray-500 truncate flex items-center gap-1">
                    <ShieldCheck className="w-3 h-3 text-[#006948]" />
                    {currentStore.city}
                  </span>
                </div>
              </div>
            ) : (
              <div className="flex items-center gap-2">
                <button
                  onClick={() => handleAuth('login')}
                  className="px-3 py-1.5 rounded-xl bg-[#006948] text-white text-xs font-bold hover:bg-[#00855d] transition-colors"
                >
                  {t.login}
                </button>
                <button
                  onClick={() => handleAuth('register')}
                  className="px-3 py-1.5 rounded-xl border border-gray-300 text-gray-700 text-xs font-bold hover:bg-gray-50 transition-colors"
                >
                  {t.register}
                </button>
              </div>
            )}
          </div>

          {/* Right Action Tools */}
          <div className="flex items-center gap-2 sm:gap-3">
            
            {/* Store Status Toggle */}
            {currentStore && (
              <div className="relative">
                <button
                  onClick={() => setIsStatusMenuOpen(!isStatusMenuOpen)}
                  className={`hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-xs font-bold ${currentStatusConfig.bg} ${currentStatusConfig.text} transition-colors border border-current/10`}
                >
                  <span className={`w-2 h-2 rounded-full ${currentStatusConfig.dot} animate-pulse`} />
                  <span>{currentStatusConfig.label}</span>
                  <ChevronDown className="w-3.5 h-3.5 opacity-70" />
                </button>

                {isStatusMenuOpen && (
                  <div 
                    className="absolute top-full mt-2 w-44 bg-white rounded-xl shadow-xl border border-gray-100 py-1 z-50 end-0"
                    onClick={(e) => e.stopPropagation()}
                  >
                    {(['open', 'busy', 'closed'] as StoreStatus[]).map(st => {
                      const cfg = statusColors[st];
                      return (
                        <button
                          key={st}
                          onClick={() => {
                            setStoreStatus(st);
                            setIsStatusMenuOpen(false);
                          }}
                          className="w-full px-3 py-2 text-right text-xs font-medium hover:bg-gray-50 flex items-center justify-between"
                        >
                          <span className="flex items-center gap-2">
                            <span className={`w-2 h-2 rounded-full ${cfg.dot}`} />
                            <span className="font-semibold text-gray-700">{cfg.label}</span>
                          </span>
                          {currentStore.status === st && <Check className="w-3.5 h-3.5 text-[#006948]" />}
                        </button>
                      );
                    })}
                  </div>
                )}
              </div>
            )}

            {/* Notification Bell with Badge */}
            <div className="relative">
              <button
                type="button"
                onClick={handleOpenNotificationPage}
                title={t.notifications}
                aria-label={t.notifications}
                className="w-9 h-9 rounded-full bg-[#f8f9ff] hover:bg-[#eff4ff] active:scale-95 flex items-center justify-center text-gray-600 hover:text-[#006948] transition-all cursor-pointer border border-[#e2e8f0] relative shadow-2xs"
              >
                <Bell className="w-4 h-4" />
                {unreadNotificationsCount > 0 ? (
                  <span className="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-[#ba1a1a] text-white text-[9px] font-extrabold flex items-center justify-center shadow-xs animate-pulse">
                    {unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount}
                  </span>
                ) : newOrdersCount > 0 ? (
                  <span className="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-[#ba1a1a] text-white text-[9px] font-bold flex items-center justify-center animate-bounce">
                    {newOrdersCount}
                  </span>
                ) : null}
              </button>
            </div>

            {/* Language Switcher */}
            <button
              onClick={toggleLanguage}
              className="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-[#f8f9ff] hover:bg-[#eff4ff] text-xs font-bold text-gray-700 border border-[#e2e8f0] transition-colors"
            >
              <Languages className="w-3.5 h-3.5 text-[#006948]" />
              <span>{language === 'ar' ? 'English' : 'عربي'}</span>
            </button>
          </div>
        </div>
      </div>
    </header>
  );
};
