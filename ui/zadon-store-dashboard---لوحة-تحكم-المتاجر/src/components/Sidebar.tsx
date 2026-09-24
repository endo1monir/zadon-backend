import React from 'react';
import { useStore } from '../context/StoreContext';
import { translations } from '../utils/translations';
import { TabKey } from '../types';
import { 
  LayoutDashboard, 
  Boxes, 
  ShoppingBag, 
  Store, 
  Clock,
  MapPin,
  Bell,
  Star,
  LogOut
} from 'lucide-react';

export type { TabKey };

interface SidebarProps {
  activeTab: TabKey;
  setActiveTab?: (tab: TabKey) => void;
  onTabChange?: (tab: TabKey) => void;
  onOpenAddProduct?: () => void;
}

export const Sidebar: React.FC<SidebarProps> = ({ 
  activeTab, 
  setActiveTab, 
  onTabChange 
}) => {
  const { 
    currentStore, 
    language, 
    lowStockProducts, 
    newOrdersCount, 
    activeOrdersCount,
    unreadNotificationsCount,
    reviews,
    logoutStore
  } = useStore();
  const t = translations[language];

  const changeTab = (tab: TabKey) => {
    if (onTabChange) onTabChange(tab);
    if (setActiveTab) setActiveTab(tab);
  };

  const navItems: { key: TabKey; label: string; icon: React.FC<{ className?: string }>; badge?: number; alert?: boolean }[] = [
    { key: 'overview', label: t.dashboard, icon: LayoutDashboard },
    { key: 'products', label: t.products, icon: Boxes, badge: lowStockProducts.length > 0 ? lowStockProducts.length : undefined, alert: lowStockProducts.length > 0 },
    { key: 'orders', label: t.orders, icon: ShoppingBag, badge: newOrdersCount > 0 ? newOrdersCount : (activeOrdersCount > 0 ? activeOrdersCount : undefined) },
    { key: 'reviews', label: t.reviews, icon: Star, badge: reviews.length > 0 ? reviews.length : undefined },
    { key: 'notifications', label: t.notifications, icon: Bell, badge: unreadNotificationsCount > 0 ? unreadNotificationsCount : undefined, alert: unreadNotificationsCount > 0 },
    { key: 'profile', label: t.storeProfile, icon: Store },
  ];

  return (
    <>
      {/* Desktop & Tablet Sidebar */}
      <aside className="hidden md:flex flex-col w-64 bg-white border-e border-[#e2e8f0] p-4 shrink-0 min-h-[calc(100vh-4rem)]">
        
        {/* Active Store Card snippet */}
        {currentStore && (
          <div className="mb-6 p-3 rounded-2xl bg-gradient-to-br from-[#eff4ff] to-[#f8f9ff] border border-[#dce9ff]">
            <div className="flex items-center gap-3 mb-2">
              <img 
                src={currentStore.logo} 
                alt={currentStore.nameAr}
                className="w-12 h-12 rounded-xl object-cover shadow-xs border border-white"
              />
              <div className="min-w-0">
                <h3 className="font-extrabold text-sm text-[#0b1c30] truncate">
                  {language === 'ar' ? currentStore.nameAr : currentStore.nameEn}
                </h3>
                <p className="text-[11px] text-gray-500 truncate flex items-center gap-1 mt-0.5">
                  <MapPin className="w-3 h-3 text-[#006948]" />
                  <span>{currentStore.addressAr}</span>
                </p>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-1.5 pt-2 border-t border-blue-100/60 text-[11px]">
              <div className="bg-white/80 rounded-lg p-1.5 text-center">
                <span className="text-gray-400 block text-[10px]">{t.prepTime}</span>
                <span className="font-bold text-[#006948]">{currentStore.prepTimeMin} {t.mins}</span>
              </div>
              <div className="bg-white/80 rounded-lg p-1.5 text-center">
                <span className="text-gray-400 block text-[10px]">{t.minOrder}</span>
                <span className="font-bold text-gray-700">{currentStore.minOrder} {t.sar}</span>
              </div>
            </div>
          </div>
        )}

        {/* Navigation Items */}
        <nav className="flex-1 space-y-1">
          {navItems.map(item => {
            const Icon = item.icon;
            const isActive = activeTab === item.key;
            return (
              <button
                key={item.key}
                onClick={() => changeTab(item.key)}
                className={`w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all ${
                  isActive
                    ? 'bg-[#006948] text-white shadow-xs'
                    : 'text-[#3d4a42] hover:bg-[#eff4ff] hover:text-[#0b1c30]'
                }`}
              >
                <div className="flex items-center gap-3">
                  <Icon className={`w-5 h-5 ${isActive ? 'text-white' : 'text-[#6d7a72]'}`} />
                  <span>{item.label}</span>
                </div>
                {item.badge !== undefined && (
                  <span
                    className={`text-[10px] font-bold px-2 py-0.5 rounded-full ${
                      isActive
                        ? 'bg-white/20 text-white'
                        : item.alert
                        ? 'bg-amber-100 text-amber-800'
                        : 'bg-[#85f8c4] text-[#002114]'
                    }`}
                  >
                    {item.badge}
                  </span>
                )}
              </button>
            );
          })}

          {/* Logout Tab */}
          <div className="pt-2 mt-2 border-t border-gray-100">
            <button
              onClick={() => {
                logoutStore();
                changeTab('overview');
              }}
              className="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all text-red-600 hover:bg-red-50 hover:text-red-700"
              title={t.logout}
            >
              <div className="flex items-center gap-3">
                <LogOut className="w-5 h-5 text-red-500" />
                <span>{t.logout}</span>
              </div>
            </button>
          </div>
        </nav>
      </aside>

      {/* Mobile Bottom Navigation Bar (Thumb ergonomic design matching Zadon screenshots) */}
      <nav className="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-[#e2e8f0] pb-safe shadow-lg">
        <div className="flex items-center justify-around h-16 px-1">
          {navItems.map(item => {
            const Icon = item.icon;
            const isActive = activeTab === item.key;
            return (
              <button
                key={item.key}
                onClick={() => changeTab(item.key)}
                className={`flex flex-col items-center justify-center min-w-[50px] py-1 transition-colors relative ${
                  isActive ? 'text-[#006948] font-bold' : 'text-gray-500 hover:text-gray-900'
                }`}
              >
                <div className="relative">
                  <Icon className={`w-5 h-5 ${isActive ? 'stroke-[2.5]' : ''}`} />
                  {item.badge !== undefined && (
                    <span className="absolute -top-1 -right-2 min-w-[16px] h-4 px-1 rounded-full bg-[#006948] text-white text-[9px] font-bold flex items-center justify-center">
                      {item.badge}
                    </span>
                  )}
                </div>
                <span className="text-[9px] sm:text-[10px] mt-0.5 truncate max-w-[58px]">{item.label}</span>
              </button>
            );
          })}
          <button
            onClick={() => {
              logoutStore();
              changeTab('overview');
            }}
            className="flex flex-col items-center justify-center min-w-[50px] py-1 transition-colors text-red-500 hover:text-red-700"
            title={t.logout}
          >
            <LogOut className="w-5 h-5" />
            <span className="text-[9px] sm:text-[10px] mt-0.5 truncate max-w-[58px]">{t.logout}</span>
          </button>
        </div>
      </nav>
    </>
  );
};
