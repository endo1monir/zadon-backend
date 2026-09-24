import React from 'react';
import { useStore } from '../../context/StoreContext';
import { translations } from '../../utils/translations';
import { TabKey } from '../../types';
import { 
  Bell, 
  ShoppingBag, 
  AlertTriangle, 
  Info, 
  Clock, 
  ChevronLeft,
  ChevronRight
} from 'lucide-react';

interface NotificationsViewProps {
  onNavigate: (tab: TabKey) => void;
  onSelectOrder?: (orderId: string) => void;
  onSelectProduct?: (productId: string) => void;
}

export const NotificationsView: React.FC<NotificationsViewProps> = ({ 
  onNavigate, 
  onSelectOrder,
  onSelectProduct
}) => {
  const { 
    notifications, 
    unreadNotificationsCount, 
    markNotificationAsRead, 
    markAllNotificationsAsRead, 
    language 
  } = useStore();

  const t = translations[language];
  const isRtl = language === 'ar';

  const formatTimeAgo = (isoString: string) => {
    try {
      const diffMs = Date.now() - new Date(isoString).getTime();
      const diffMins = Math.floor(diffMs / (60 * 1000));
      const diffHours = Math.floor(diffMins / 60);
      const diffDays = Math.floor(diffHours / 24);

      if (diffMins < 1) return isRtl ? 'الآن' : 'Just now';
      if (diffMins < 60) return isRtl ? `منذ ${diffMins} دقيقة` : `${diffMins}m ago`;
      if (diffHours < 24) return isRtl ? `منذ ${diffHours} ساعة` : `${diffHours}h ago`;
      return isRtl ? `منذ ${diffDays} يوم` : `${diffDays}d ago`;
    } catch {
      return '';
    }
  };

  const handleNotificationClick = (notif: typeof notifications[0]) => {
    markNotificationAsRead(notif.id);
    if (notif.orderId) {
      if (onSelectOrder) {
        onSelectOrder(notif.orderId);
      } else {
        onNavigate('orders');
      }
    } else if (notif.productId) {
      if (onSelectProduct) {
        onSelectProduct(notif.productId);
      } else {
        onNavigate('products');
      }
    }
  };

  return (
    <div className="max-w-3xl mx-auto space-y-4">
      {/* Simple Header */}
      <div className="flex items-center justify-between py-2">
        <div className="flex items-center gap-2.5">
          <div className="w-9 h-9 rounded-xl bg-[#006948] text-white flex items-center justify-center shadow-xs">
            <Bell className="w-5 h-5" />
          </div>
          <div>
            <h2 className="text-xl font-black text-[#0b1c30] tracking-tight">
              {t.notifications}
            </h2>
          </div>
        </div>
      </div>

      {/* Notifications List */}
      <div className="bg-white rounded-2xl border border-[#e2e8f0] shadow-xs overflow-hidden">
        {notifications.length === 0 ? (
          <div className="py-16 px-4 text-center">
            <div className="w-12 h-12 rounded-2xl bg-gray-50 text-gray-400 flex items-center justify-center mx-auto mb-3">
              <Bell className="w-6 h-6 opacity-60" />
            </div>
            <p className="text-sm font-bold text-gray-700">{t.noNotifications}</p>
            <p className="text-xs text-gray-400 mt-1">{t.noNotificationsDesc}</p>
          </div>
        ) : (
          <div className="divide-y divide-gray-100">
            {notifications.map((notif) => {
              const isOrder = notif.type === 'order';
              const isInventory = notif.type === 'inventory';
              const title = isRtl ? notif.titleAr : notif.titleEn;
              const message = isRtl ? notif.messageAr : notif.messageEn;

              return (
                <div
                  key={notif.id}
                  onClick={() => handleNotificationClick(notif)}
                  className={`p-4 transition-colors flex items-start gap-3.5 cursor-pointer ${
                    !notif.isRead 
                      ? 'bg-[#f0fdf4]/50 hover:bg-[#f0fdf4]' 
                      : 'hover:bg-[#f8f9ff]'
                  }`}
                >
                  {/* Type Icon */}
                  <div className={`w-9 h-9 rounded-xl flex items-center justify-center shrink-0 mt-0.5 ${
                    isOrder 
                      ? 'bg-[#ecfdf5] text-[#006948]' 
                      : isInventory 
                      ? 'bg-amber-50 text-amber-700' 
                      : 'bg-blue-50 text-blue-700'
                  }`}>
                    {isOrder ? (
                      <ShoppingBag className="w-4 h-4" />
                    ) : isInventory ? (
                      <AlertTriangle className="w-4 h-4" />
                    ) : (
                      <Info className="w-4 h-4" />
                    )}
                  </div>

                  {/* Content */}
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center justify-between gap-2">
                      <div className="flex items-center gap-2">
                        <h4 className={`text-sm tracking-tight ${!notif.isRead ? 'font-black text-[#0b1c30]' : 'font-bold text-gray-700'}`}>
                          {title}
                        </h4>
                        {!notif.isRead && (
                          <span className="w-2 h-2 rounded-full bg-[#006948] shrink-0" />
                        )}
                      </div>
                      <div className="flex items-center gap-1 text-[11px] text-gray-400 shrink-0">
                        <Clock className="w-3 h-3" />
                        <span>{formatTimeAgo(notif.timestamp)}</span>
                      </div>
                    </div>

                    <p className="text-xs text-gray-600 mt-1 leading-relaxed line-clamp-2">
                      {message}
                    </p>
                  </div>

                  {/* Navigation Arrow */}
                  <div className="text-gray-300 self-center shrink-0">
                    {isRtl ? <ChevronLeft className="w-4 h-4" /> : <ChevronRight className="w-4 h-4" />}
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>
    </div>
  );
};
