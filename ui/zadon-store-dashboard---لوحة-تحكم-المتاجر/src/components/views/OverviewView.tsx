import React from 'react';
import { useStore } from '../../context/StoreContext';
import { translations } from '../../utils/translations';
import { TabKey } from '../Sidebar';
import { 
  Store,
  TrendingUp, 
  ShoppingBag, 
  AlertTriangle, 
  Clock, 
  Package, 
  ArrowRight, 
  CheckCircle2, 
  ChevronRight, 
  Plus, 
  RefreshCw,
  Sparkles,
  Zap,
  Bike,
  LogIn
} from 'lucide-react';

interface OverviewViewProps {
  onNavigate: (tab: TabKey) => void;
  onOpenAddProduct: () => void;
  onSelectOrder: (orderId: string) => void;
  onOpenLogin?: () => void;
}

export const OverviewView: React.FC<OverviewViewProps> = ({ 
  onNavigate, 
  onOpenAddProduct,
  onSelectOrder,
  onOpenLogin
}) => {
  const { 
    currentStore, 
    stores,
    loginStore,
    products, 
    orders, 
    language, 
    todayRevenue, 
    activeOrdersCount, 
    lowStockProducts, 
    outOfStockProducts,
    newOrdersCount,
    updateOrderStatus,
    adjustStock,
    simulateIncomingOrder
  } = useStore();

  const t = translations[language];

  // Recent 4 orders
  const recentOrders = [...orders].sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()).slice(0, 4);

  // Status badge helper
  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'new':
        return <span className="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 animate-pulse">{t.orderStatusNew}</span>;
      case 'preparing':
        return <span className="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">{t.orderStatusPreparing}</span>;
      case 'ready_for_pickup':
        return <span className="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">{t.orderStatusReady}</span>;
      case 'out_for_delivery':
        return <span className="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1"><Bike className="w-3 h-3" />{t.orderStatusOutForDelivery}</span>;
      case 'delivered':
        return <span className="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">{t.orderStatusDelivered}</span>;
      default:
        return <span className="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">{t.orderStatusCancelled}</span>;
    }
  };

  if (!currentStore) {
    return (
      <div className="p-8 sm:p-12 text-center bg-white rounded-3xl border border-[#e2e8f0] shadow-xs max-w-lg mx-auto my-12 space-y-4">
        <div className="w-16 h-16 rounded-2xl bg-[#ecfdf5] text-[#006948] flex items-center justify-center mx-auto shadow-2xs">
          <Store className="w-8 h-8" />
        </div>
        <h2 className="text-xl font-extrabold text-[#0b1c30]">
          {language === 'ar' ? 'تم تسجيل الخروج من المتجر' : 'You are currently logged out'}
        </h2>
        <p className="text-xs text-gray-500 max-w-sm mx-auto leading-relaxed">
          {language === 'ar' 
            ? 'يمكنك تسجيل متجر جديد للانضمام لمنظومة زادون أو اختيار أحد المتاجر المسجلة للدخول فوراً.' 
            : 'You can register a new merchant store or log into an existing registered store.'}
        </p>
        <div className="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
          <button
            onClick={() => onNavigate('register')}
            className="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#006948] hover:bg-[#00855d] text-white text-xs font-bold transition-all shadow-xs"
          >
            {language === 'ar' ? 'تسجيل متجر جديد' : 'Register New Store'}
          </button>
          <button
            onClick={() => onOpenLogin && onOpenLogin()}
            className="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-2xs"
          >
            <LogIn className="w-3.5 h-3.5 text-[#006948]" />
            <span>
              {language === 'ar' ? 'تسجيل الدخول (رقم الجوال وكلمة المرور)' : 'Log In (Phone & Password)'}
            </span>
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      
      {/* Welcome Banner matching Zadon's emerald gradient aesthetic */}
      <div className="relative overflow-hidden rounded-3xl bg-gradient-to-l from-[#006948] via-[#00855d] to-[#005137] p-6 text-white shadow-md">
        <div className="absolute -left-10 -bottom-10 w-44 h-44 rounded-full bg-white/10 pointer-events-none blur-xl"></div>
        <div className="absolute left-20 -top-10 w-28 h-28 rounded-full bg-white/5 pointer-events-none blur-lg"></div>

        <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <div className="flex items-center gap-2 mb-2">
              <span className="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-white/20 text-white text-xs font-bold backdrop-blur-sm shadow-xs">
                <Zap className="w-3.5 h-3.5 text-[#85f8c4]" />
                <span>{currentStore.isVerifiedPartner ? (language === 'ar' ? 'شريك زادون المعتمد' : 'Verified Partner') : 'شريك زادون'}</span>
              </span>
              <span className="text-xs text-white/80">• {currentStore.city}</span>
            </div>
            <h1 className="text-2xl sm:text-3xl font-black tracking-tight mb-1">
              {language === 'ar' ? `لوحة تحكم ${currentStore.nameAr}` : `${currentStore.nameEn} Dashboard`}
            </h1>
            <p className="text-sm text-white/85 max-w-xl">
              {language === 'ar' 
                ? 'متابعة حية وفورية لحركة المبيعات، تحديث جاهزية الطلبات وتنبيهات مستويات المخزون للتوصيل السريع.'
                : 'Real-time live monitoring of sales, fast fulfillment order updates, and inventory depletion alerts.'}
            </p>
          </div>

          {/* Quick Primary Actions in Banner */}
          <div className="flex flex-wrap items-center gap-2">
            <button
              onClick={onOpenAddProduct}
              className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-[#006948] font-bold text-sm shadow-sm hover:bg-[#f8f9ff] active:scale-95 transition-all"
            >
              <Plus className="w-4 h-4" />
              <span>{t.addProduct}</span>
            </button>
            <button
              onClick={() => onNavigate('orders')}
              className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#85f8c4]/25 hover:bg-[#85f8c4]/35 text-white font-bold text-sm border border-white/20 backdrop-blur-xs transition-all"
            >
              <ShoppingBag className="w-4 h-4" />
              <span>{t.orders} ({activeOrdersCount})</span>
            </button>
          </div>
        </div>
      </div>


      {/* Primary KPI Cards Grid */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        
        {/* KPI 1: Today Sales */}
        <div className="bg-white p-4 rounded-2xl border border-[#e2e8f0] shadow-xs flex flex-col justify-between">
          <div className="flex items-center justify-between text-gray-500 mb-2">
            <span className="text-xs font-bold text-[#3d4a42]">{t.todaySales}</span>
            <div className="w-8 h-8 rounded-lg bg-[#ecfdf5] text-[#006948] flex items-center justify-center">
              <TrendingUp className="w-4 h-4" />
            </div>
          </div>
          <div>
            <div className="flex items-baseline gap-1">
              <span className="text-2xl sm:text-3xl font-black text-[#0b1c30]">
                {todayRevenue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </span>
              <span className="text-xs font-bold text-[#006948]">{t.sar}</span>
            </div>
            <span className="text-[11px] text-gray-400 block mt-1">{t.vatIncluded}</span>
          </div>
        </div>

        {/* KPI 2: Active Orders */}
        <div className="bg-white p-4 rounded-2xl border border-[#e2e8f0] shadow-xs flex flex-col justify-between">
          <div className="flex items-center justify-between text-gray-500 mb-2">
            <span className="text-xs font-bold text-[#3d4a42]">{t.activeOrders}</span>
            <div className="w-8 h-8 rounded-lg bg-[#eff4ff] text-[#006194] flex items-center justify-center">
              <ShoppingBag className="w-4 h-4" />
            </div>
          </div>
          <div>
            <div className="flex items-baseline gap-2">
              <span className="text-2xl sm:text-3xl font-black text-[#0b1c30]">{activeOrdersCount}</span>
              <span className="text-xs text-gray-500">
                ({orders.length} {language === 'ar' ? 'إجمالي اليوم' : 'total today'})
              </span>
            </div>
            <div className="flex items-center gap-1 text-[11px] text-[#006948] font-bold mt-1">
              <Clock className="w-3 h-3" />
              <span>{t.prepTime}: {currentStore.prepTimeMin} {t.mins}</span>
            </div>
          </div>
        </div>

        {/* KPI 3: Low Stock Alerts */}
        <div className="bg-white p-4 rounded-2xl border border-[#e2e8f0] shadow-xs flex flex-col justify-between">
          <div className="flex items-center justify-between text-gray-500 mb-2">
            <span className="text-xs font-bold text-[#3d4a42]">{t.lowStockWarning}</span>
            <div className="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
              <AlertTriangle className="w-4 h-4" />
            </div>
          </div>
          <div>
            <div className="flex items-baseline gap-2">
              <span className={`text-2xl sm:text-3xl font-black ${lowStockProducts.length > 0 ? 'text-amber-600' : 'text-[#0b1c30]'}`}>
                {lowStockProducts.length}
              </span>
              {outOfStockProducts.length > 0 && (
                <span className="text-xs font-bold text-red-600 bg-red-50 px-1.5 py-0.5 rounded">
                  {outOfStockProducts.length} {t.outOfStock}
                </span>
              )}
            </div>
            <span className="text-[11px] text-gray-400 block mt-1">
              {lowStockProducts.length === 0 ? 'جميع المخزون بمستوى آمن' : 'أصناف بحاجة لتوريد عاجل'}
            </span>
          </div>
        </div>

        {/* KPI 4: Total Inventory Items */}
        <div className="bg-white p-4 rounded-2xl border border-[#e2e8f0] shadow-xs flex flex-col justify-between">
          <div className="flex items-center justify-between text-gray-500 mb-2">
            <span className="text-xs font-bold text-[#3d4a42]">{language === 'ar' ? 'إجمالي الأصناف' : 'Total SKUs'}</span>
            <div className="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
              <Package className="w-4 h-4" />
            </div>
          </div>
          <div>
            <div className="flex items-baseline gap-2">
              <span className="text-2xl sm:text-3xl font-black text-[#0b1c30]">{products.length}</span>
              <span className="text-xs text-gray-400">
                {products.reduce((acc, p) => acc + p.stock, 0)} {language === 'ar' ? 'قطعة بالمخزن' : 'units in stock'}
              </span>
            </div>
            <button 
              onClick={() => onNavigate('analytics')}
              className="text-[11px] text-[#006948] font-bold hover:underline flex items-center gap-1 mt-1"
            >
              <span>{language === 'ar' ? 'عرض تقرير المخزون' : 'Stock Health Report'}</span>
              <ChevronRight className="w-3 h-3" />
            </button>
          </div>
        </div>

      </div>

      {/* Main Grid: Live Orders & Critical Stock Alerts */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {/* Left 2 Cols: Live Orders Stream */}
        <div className="lg:col-span-2 bg-white rounded-3xl border border-[#e2e8f0] p-5 shadow-xs flex flex-col">
          <div className="flex items-center justify-between pb-4 mb-3 border-b border-[#e2e8f0]">
            <div>
              <h3 className="font-extrabold text-base text-[#0b1c30] flex items-center gap-2">
                <ShoppingBag className="w-5 h-5 text-[#006948]" />
                <span>{t.orders}</span>
                <span className="text-xs font-bold px-2 py-0.5 rounded-full bg-[#eff4ff] text-[#006194]">
                  {orders.length}
                </span>
              </h3>
              <p className="text-xs text-gray-400 mt-0.5">
                {language === 'ar' ? 'الطلبات المباشرة والتحديث الفوري لمندوبي التوصيل والعملاء' : 'Live order stream & instant updates'}
              </p>
            </div>
            <button
              onClick={() => onNavigate('orders')}
              className="text-xs font-bold text-[#006948] hover:underline flex items-center gap-1"
            >
              <span>{language === 'ar' ? 'كافة الطلبات' : 'View All'}</span>
              <ArrowRight className="w-3.5 h-3.5" />
            </button>
          </div>

          {/* Orders List */}
          <div className="space-y-3 flex-1">
            {recentOrders.length === 0 ? (
              <div className="text-center py-10 text-gray-400">
                <ShoppingBag className="w-10 h-10 mx-auto mb-2 opacity-30" />
                <p className="text-sm">لا توجد طلبات بعد لهذا المتجر</p>
                <button
                  onClick={() => simulateIncomingOrder()}
                  className="mt-3 text-xs text-[#006948] font-bold hover:underline"
                >
                  اضغط هنا لمحاكاة طلب تجريبي
                </button>
              </div>
            ) : (
              recentOrders.map(order => (
                <div
                  key={order.id}
                  onClick={() => onSelectOrder(order.id)}
                  className="p-3.5 rounded-2xl bg-[#f8f9ff] hover:bg-[#eff4ff] border border-[#e2e8f0] transition-all cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-3 group"
                >
                  <div className="flex items-start gap-3">
                    <div className="w-10 h-10 rounded-xl bg-white border border-[#dce9ff] flex items-center justify-center font-bold text-xs text-[#006948] shrink-0 shadow-2xs">
                      #{order.orderNumber.split('-')[1] || order.orderNumber}
                    </div>
                    <div>
                      <div className="flex items-center gap-2">
                        <span className="font-extrabold text-sm text-[#0b1c30] group-hover:text-[#006948] transition-colors">
                          {order.customerName}
                        </span>
                        <span className="text-[11px] text-gray-400">
                          {new Date(order.createdAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                        </span>
                      </div>
                      <p className="text-xs text-gray-500 truncate max-w-xs sm:max-w-md mt-0.5">
                        {order.items.map(i => `${i.productNameAr} (${i.quantity})`).join('، ')}
                      </p>
                      <span className="text-[11px] text-gray-400 flex items-center gap-1 mt-0.5">
                        📍 {order.deliveryAddress}
                      </span>
                    </div>
                  </div>

                  <div className="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-200">
                    <div className="flex items-center gap-1.5">
                      {getStatusBadge(order.status)}
                    </div>
                    <div className="text-left">
                      <span className="font-black text-sm text-[#0b1c30]">{order.total.toFixed(2)}</span>
                      <span className="text-[10px] font-bold text-gray-500 mr-0.5"> {t.sar}</span>
                    </div>
                  </div>
                </div>
              ))
            )}
          </div>
        </div>

        {/* Right 1 Col: Urgent Inventory Depletion & Restock */}
        <div className="bg-white rounded-3xl border border-[#e2e8f0] p-5 shadow-xs flex flex-col">
          <div className="flex items-center justify-between pb-3 mb-3 border-b border-[#e2e8f0]">
            <div>
              <h3 className="font-extrabold text-base text-[#0b1c30] flex items-center gap-2">
                <AlertTriangle className="w-5 h-5 text-amber-500" />
                <span>{t.lowStockWarning}</span>
              </h3>
              <p className="text-xs text-gray-400 mt-0.5">
                {language === 'ar' ? 'منتجات أوشكت كميتها على النفاد' : 'Restock before stockout'}
              </p>
            </div>
            <button
              onClick={() => onNavigate('products')}
              className="text-xs font-bold text-[#006948] hover:underline"
            >
              {language === 'ar' ? 'المخزن' : 'All'}
            </button>
          </div>

          <div className="space-y-3 flex-1">
            {lowStockProducts.length === 0 && outOfStockProducts.length === 0 ? (
              <div className="text-center py-8 text-gray-400 flex flex-col items-center">
                <CheckCircle2 className="w-10 h-10 text-emerald-500 mb-2 opacity-80" />
                <span className="font-bold text-sm text-gray-700">المخزون مكتمل وسليم</span>
                <span className="text-xs text-gray-400 mt-1">لا توجد منتجات منخفضة في الوقت الحالي</span>
              </div>
            ) : (
              [...outOfStockProducts, ...lowStockProducts].slice(0, 5).map(prod => {
                const isOut = prod.stock === 0;
                return (
                  <div 
                    key={prod.id}
                    className={`p-3 rounded-xl border flex items-center justify-between gap-2 ${
                      isOut ? 'bg-red-50/50 border-red-200' : 'bg-amber-50/40 border-amber-200'
                    }`}
                  >
                    <div className="flex items-center gap-2.5 min-w-0">
                      <img 
                        src={prod.image} 
                        alt={prod.nameAr}
                        className="w-10 h-10 rounded-lg object-cover bg-white border shrink-0"
                      />
                      <div className="min-w-0">
                        <h4 className="font-bold text-xs text-[#0b1c30] truncate">{prod.nameAr}</h4>
                        <div className="flex items-center gap-2 mt-0.5 text-[11px]">
                          <span className={`font-black ${isOut ? 'text-red-700' : 'text-amber-700'}`}>
                            {isOut ? t.outOfStock : `${prod.stock} ${prod.unitAr}`}
                          </span>
                          <span className="text-gray-400">• حد التنبيه: {prod.minStockAlert}</span>
                        </div>
                      </div>
                    </div>

                    {/* Quick +10 Restock Button */}
                    <button
                      onClick={() => adjustStock(prod.id, 10, 'توريد سريع فوري من الشاشة الرئيسية')}
                      title="إضافة 10 قطع فوراً للمخزون"
                      className="px-2.5 py-1.5 rounded-lg bg-white border border-gray-300 hover:border-[#006948] hover:text-[#006948] text-xs font-bold text-gray-700 shrink-0 shadow-2xs active:scale-95 transition-all flex items-center gap-1"
                    >
                      <Plus className="w-3.5 h-3.5" />
                      <span>+10</span>
                    </button>
                  </div>
                );
              })
            )}
          </div>

          <div className="mt-4 pt-3 border-t border-gray-100">
            <button
              onClick={() => onNavigate('analytics')}
              className="w-full py-2.5 rounded-xl bg-[#eff4ff] hover:bg-[#dce9ff] text-[#006194] text-xs font-extrabold flex items-center justify-center gap-2 transition-colors"
            >
              <span>{t.depletionForecast}</span>
              <ArrowRight className="w-3.5 h-3.5" />
            </button>
          </div>
        </div>

      </div>

    </div>
  );
};
