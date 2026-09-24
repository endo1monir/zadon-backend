import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { translations } from '../../utils/translations';
import { Order, OrderStatus } from '../../types';
import { 
  ShoppingBag, 
  Search, 
  Clock, 
  MapPin, 
  Phone, 
  Printer, 
  CheckCircle, 
  ArrowRight, 
  Bike, 
  User, 
  FileText, 
  Check, 
  Sparkles,
  QrCode,
  ShieldCheck,
  AlertCircle
} from 'lucide-react';

interface OrdersViewProps {
  selectedOrderId: string | null;
  setSelectedOrderId: (id: string | null) => void;
}

export const OrdersView: React.FC<OrdersViewProps> = ({ selectedOrderId, setSelectedOrderId }) => {
  const { 
    currentStore, 
    orders, 
    language, 
    updateOrderStatus, 
    toggleOrderItemPacked,
    simulateIncomingOrder
  } = useStore();

  const t = translations[language];

  const [activeFilter, setActiveFilter] = useState<OrderStatus | 'all'>('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [isInvoiceModalOpen, setIsInvoiceModalOpen] = useState(false);
  const [invoiceOrder, setInvoiceOrder] = useState<Order | null>(null);

  // Filtered orders
  const filteredOrders = orders.filter(order => {
    const matchesFilter = activeFilter === 'all' || order.status === activeFilter;
    const matchesSearch = 
      order.orderNumber.toLowerCase().includes(searchQuery.toLowerCase()) ||
      order.customerName.toLowerCase().includes(searchQuery.toLowerCase()) ||
      order.customerPhone.includes(searchQuery);
    return matchesFilter && matchesSearch;
  });

  const getStatusBadge = (status: OrderStatus) => {
    switch (status) {
      case 'new':
        return (
          <span className="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 animate-pulse border border-amber-300">
            <span className="w-2 h-2 rounded-full bg-amber-500"></span>
            <span>{t.orderStatusNew}</span>
          </span>
        );
      case 'preparing':
        return (
          <span className="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-900 border border-blue-300">
            <Clock className="w-3.5 h-3.5 text-blue-600 animate-spin" />
            <span>{t.orderStatusPreparing}</span>
          </span>
        );
      case 'ready_for_pickup':
        return (
          <span className="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-900 border border-purple-300">
            <Check className="w-3.5 h-3.5 text-purple-600" />
            <span>{t.orderStatusReady}</span>
          </span>
        );
      case 'out_for_delivery':
        return (
          <span className="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-300">
            <Bike className="w-3.5 h-3.5 text-emerald-700" />
            <span>{t.orderStatusOutForDelivery}</span>
          </span>
        );
      case 'delivered':
        return (
          <span className="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
            <CheckCircle className="w-3.5 h-3.5 text-emerald-600" />
            <span>{t.orderStatusDelivered}</span>
          </span>
        );
      case 'cancelled':
        return (
          <span className="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
            <span>{t.orderStatusCancelled}</span>
          </span>
        );
    }
  };

  // Status progression button logic
  const renderNextActionButton = (order: Order) => {
    switch (order.status) {
      case 'new':
        return (
          <button
            onClick={() => updateOrderStatus(order.id, 'preparing')}
            className="w-full sm:w-auto px-4 py-2 rounded-xl bg-[#006948] hover:bg-[#00855d] text-white text-xs font-bold shadow-xs active:scale-95 transition-all flex items-center justify-center gap-1.5"
          >
            <Check className="w-4 h-4" />
            <span>{t.acceptOrder}</span>
          </button>
        );
      case 'preparing':
        return (
          <button
            onClick={() => updateOrderStatus(order.id, 'ready_for_pickup')}
            className="w-full sm:w-auto px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all flex items-center justify-center gap-1.5"
          >
            <CheckCircle className="w-4 h-4" />
            <span>{t.markAsReady}</span>
          </button>
        );
      case 'ready_for_pickup':
        return (
          <button
            onClick={() => updateOrderStatus(order.id, 'out_for_delivery')}
            className="w-full sm:w-auto px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all flex items-center justify-center gap-1.5"
          >
            <Bike className="w-4 h-4" />
            <span>{t.handoverToCourier}</span>
          </button>
        );
      case 'out_for_delivery':
        return (
          <button
            onClick={() => updateOrderStatus(order.id, 'delivered')}
            className="w-full sm:w-auto px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all flex items-center justify-center gap-1.5"
          >
            <CheckCircle className="w-4 h-4" />
            <span>{t.markDelivered}</span>
          </button>
        );
      default:
        return null;
    }
  };

  const handleOpenInvoice = (order: Order) => {
    setInvoiceOrder(order);
    setIsInvoiceModalOpen(true);
  };

  return (
    <div className="space-y-6">
      
      {/* View Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-xl sm:text-2xl font-black text-[#0b1c30] tracking-tight flex items-center gap-2">
            <ShoppingBag className="w-6 h-6 text-[#006948]" />
            <span>{t.orders}</span>
            <span className="text-xs font-bold px-2.5 py-1 rounded-full bg-[#eff4ff] text-[#006194]">
              {orders.length} {language === 'ar' ? 'طلب مسجل' : 'Orders'}
            </span>
          </h2>
          <p className="text-xs text-gray-500 mt-1">
            {language === 'ar' 
              ? 'متابعة لحظية لطلبات العملاء، تأكيد التجهيز والتسليم لمندوبي توصيل زادون.' 
              : 'Live order tracking, packaging fulfillment, and courier handovers.'}
          </p>
        </div>
      </div>

      {/* Filter and Search Bar */}
      <div className="bg-white p-4 rounded-2xl border border-[#e2e8f0] shadow-xs space-y-3">
        <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
          
          <div className="relative flex-1">
            <Search className="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder={t.searchOrders}
              className="w-full bg-[#f8f9ff] text-xs sm:text-sm text-[#0b1c30] placeholder-gray-400 pr-9 pl-3 py-2.5 rounded-xl border border-[#e2e8f0] focus:outline-none focus:border-[#006948] focus:bg-white transition-all"
            />
          </div>

          {/* Status Tabs */}
          <div className="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 text-xs font-bold">
            <button
              onClick={() => setActiveFilter('all')}
              className={`px-3 py-1.5 rounded-xl whitespace-nowrap transition-all ${
                activeFilter === 'all' ? 'bg-[#006948] text-white' : 'bg-[#f8f9ff] text-gray-600 hover:bg-gray-100'
              }`}
            >
              {t.allItems} ({orders.length})
            </button>
            <button
              onClick={() => setActiveFilter('new')}
              className={`px-3 py-1.5 rounded-xl whitespace-nowrap transition-all flex items-center gap-1 ${
                activeFilter === 'new' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-800'
              }`}
            >
              <span>{t.orderStatusNew}</span>
              <span className="w-4 h-4 rounded-full bg-white text-amber-900 text-[10px] flex items-center justify-center font-black">
                {orders.filter(o => o.status === 'new').length}
              </span>
            </button>
            <button
              onClick={() => setActiveFilter('preparing')}
              className={`px-3 py-1.5 rounded-xl whitespace-nowrap transition-all ${
                activeFilter === 'preparing' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-800'
              }`}
            >
              {t.orderStatusPreparing} ({orders.filter(o => o.status === 'preparing').length})
            </button>
            <button
              onClick={() => setActiveFilter('ready_for_pickup')}
              className={`px-3 py-1.5 rounded-xl whitespace-nowrap transition-all ${
                activeFilter === 'ready_for_pickup' ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-800'
              }`}
            >
              {t.orderStatusReady} ({orders.filter(o => o.status === 'ready_for_pickup').length})
            </button>
            <button
              onClick={() => setActiveFilter('out_for_delivery')}
              className={`px-3 py-1.5 rounded-xl whitespace-nowrap transition-all ${
                activeFilter === 'out_for_delivery' ? 'bg-emerald-700 text-white' : 'bg-emerald-50 text-emerald-800'
              }`}
            >
              {t.orderStatusOutForDelivery} ({orders.filter(o => o.status === 'out_for_delivery').length})
            </button>
            <button
              onClick={() => setActiveFilter('delivered')}
              className={`px-3 py-1.5 rounded-xl whitespace-nowrap transition-all ${
                activeFilter === 'delivered' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600'
              }`}
            >
              {t.orderStatusDelivered} ({orders.filter(o => o.status === 'delivered').length})
            </button>
          </div>
        </div>
      </div>

      {/* Orders List */}
      <div className="space-y-4">
        {filteredOrders.length === 0 ? (
          <div className="bg-white rounded-3xl p-12 text-center text-gray-400 border border-[#e2e8f0]">
            <ShoppingBag className="w-12 h-12 mx-auto mb-3 opacity-30" />
            <h4 className="text-base font-bold text-gray-700">لا توجد طلبات تطابق هذا التصنيف</h4>
            <p className="text-xs text-gray-400 mt-1">
              اختر تبويباً آخر أو قم بمحاكاة طلب عميل جديد للتجربة.
            </p>
            <button
              onClick={simulateIncomingOrder}
              className="mt-4 px-4 py-2 rounded-xl bg-[#006948] text-white text-xs font-bold"
            >
              {t.simulateOrder}
            </button>
          </div>
        ) : (
          filteredOrders.map(order => {
            return (
              <div 
                key={order.id}
                className={`bg-white rounded-3xl border p-5 shadow-xs transition-all ${
                  order.status === 'new' ? 'border-amber-300 ring-2 ring-amber-100' : 'border-[#e2e8f0]'
                }`}
              >
                {/* Header Row: Order ID, Status, Timestamp */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                  <div className="flex items-center gap-3">
                    <div className="w-11 h-11 rounded-2xl bg-[#f8f9ff] border border-[#dce9ff] flex items-center justify-center font-black text-sm text-[#006948]">
                      #{order.orderNumber}
                    </div>
                    <div>
                      <div className="flex items-center gap-2">
                        <span className="font-extrabold text-sm text-[#0b1c30]">{order.customerName}</span>
                        <a 
                          href={`tel:${order.customerPhone}`}
                          className="text-xs text-[#006948] font-bold flex items-center gap-1 hover:underline"
                        >
                          <Phone className="w-3 h-3" />
                          <span dir="ltr">{order.customerPhone}</span>
                        </a>
                      </div>
                      <span className="text-[11px] text-gray-400 flex items-center gap-1 mt-0.5">
                        <Clock className="w-3 h-3" />
                        <span>{new Date(order.createdAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                        <span>• {order.city}</span>
                      </span>
                    </div>
                  </div>

                  <div className="flex items-center gap-2">
                    {getStatusBadge(order.status)}
                    <button
                      onClick={() => handleOpenInvoice(order)}
                      title={t.printInvoice}
                      className="p-2 rounded-xl bg-[#f8f9ff] hover:bg-[#eff4ff] text-gray-700 text-xs font-bold border border-gray-200 flex items-center gap-1 transition-colors"
                    >
                      <FileText className="w-4 h-4 text-[#006948]" />
                      <span className="hidden sm:inline">{t.orderDetails}</span>
                    </button>
                  </div>
                </div>

                {/* Delivery Address & Courier Snippet */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-3 py-3 text-xs">
                  <div className="flex items-start gap-2 bg-[#f8f9ff] p-2.5 rounded-xl border border-gray-100">
                    <MapPin className="w-4 h-4 text-[#006948] shrink-0 mt-0.5" />
                    <div>
                      <span className="font-bold text-gray-700 block">{t.deliveryAddress}</span>
                      <span className="text-gray-500">{order.deliveryAddress}</span>
                      {order.notes && (
                        <p className="text-[11px] text-amber-700 mt-1 font-medium bg-amber-50 px-2 py-0.5 rounded">
                          ملاحظة العميل: {order.notes}
                        </p>
                      )}
                    </div>
                  </div>

                  {order.courierName && (
                    <div className="flex items-center justify-between bg-[#eff4ff] p-2.5 rounded-xl border border-[#dce9ff]">
                      <div className="flex items-center gap-2.5">
                        <div className="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs">
                          <Bike className="w-4 h-4" />
                        </div>
                        <div>
                          <span className="font-bold text-gray-800 block text-xs">{order.courierName}</span>
                          <span className="text-[11px] text-gray-500">كابتن زادون • وصول متوقع {order.courierEtaMinutes || 15} دقيقة</span>
                        </div>
                      </div>
                      {order.courierPhone && (
                        <a 
                          href={`tel:${order.courierPhone}`}
                          className="px-2.5 py-1 rounded-lg bg-white border text-gray-700 font-bold text-xs hover:bg-gray-50 flex items-center gap-1"
                        >
                          <Phone className="w-3 h-3 text-[#006948]" />
                          <span>اتصال</span>
                        </a>
                      )}
                    </div>
                  )}
                </div>

                {/* Footer: Payment Summary & Action Button */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-gray-100 mt-2">
                  <div className="flex items-center gap-3 text-xs">
                    <span className="px-2 py-1 rounded bg-[#f8f9ff] text-gray-700 font-bold border">
                      الدفع: {order.paymentMethod === 'mada' ? 'مدى (mada)' : order.paymentMethod === 'apple_pay' ? 'Apple Pay' : 'نقدي'}
                    </span>
                    <div className="flex items-baseline gap-1">
                      <span className="text-gray-500">الإجمالي:</span>
                      <span className="text-base font-black text-[#0b1c30]">{order.total.toFixed(2)}</span>
                      <span className="text-[10px] font-bold text-gray-500">{t.sar}</span>
                    </div>
                  </div>

                  <div className="flex items-center gap-2">
                    {renderNextActionButton(order)}
                    {order.status !== 'cancelled' && order.status !== 'delivered' && (
                      <button
                        onClick={() => updateOrderStatus(order.id, 'cancelled')}
                        className="px-3 py-2 rounded-xl text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors"
                      >
                        إلغاء الطلب
                      </button>
                    )}
                  </div>
                </div>

              </div>
            );
          })
        )}
      </div>

      {/* Tax Invoice & Packing Modal (Compliant with Saudi ZATCA as shown in screenshots) */}
      {isInvoiceModalOpen && invoiceOrder && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in duration-200">
          <div className="bg-white rounded-3xl w-full max-w-lg max-h-[95vh] overflow-y-auto shadow-2xl p-6 relative">
            
            {/* Close button */}
            <button
              onClick={() => setIsInvoiceModalOpen(false)}
              className="absolute top-4 left-4 w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 no-print"
            >
              ✕
            </button>

            {/* Print Header */}
            <div className="text-center pb-4 mb-4 border-b border-gray-200">
              <div className="w-12 h-12 rounded-2xl bg-[#006948] text-white flex items-center justify-center mx-auto mb-2 font-black text-xl">
                ز
              </div>
              <h3 className="font-black text-lg text-[#0b1c30]">تطبيق زادون • {currentStore?.nameAr}</h3>
              <p className="text-xs text-gray-500">{t.taxInvoiceZatca}</p>
              <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#ecfdf5] text-[#006948] text-xs font-bold mt-2">
                <ShieldCheck className="w-3.5 h-3.5" />
                <span>فاتورة ضريبية إلكترونية نظامية</span>
              </div>
            </div>

            {/* Invoice Meta */}
            <div className="bg-[#f8f9ff] rounded-2xl p-3.5 border border-gray-200 space-y-1.5 text-xs text-gray-600 mb-4">
              <div className="flex justify-between">
                <span className="text-gray-400">رقم الطلب:</span>
                <span className="font-bold text-[#0b1c30]">#{invoiceOrder.orderNumber}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-400">الرقم الضريبي للمنشأة:</span>
                <span className="font-mono font-bold text-[#0b1c30]">{currentStore?.vatNumber || '310123456700003'}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-400">رقم السجل التجاري:</span>
                <span className="font-mono font-bold text-[#0b1c30]">{currentStore?.crNumber || '1010234901'}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-400">التاريخ والوقت:</span>
                <span className="font-bold text-[#0b1c30]">{new Date(invoiceOrder.createdAt).toLocaleString()}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-400">اسم العميل:</span>
                <span className="font-bold text-[#0b1c30]">{invoiceOrder.customerName}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-400">عنوان التوصيل:</span>
                <span className="font-bold text-[#0b1c30] text-left">{invoiceOrder.deliveryAddress}</span>
              </div>
            </div>

            {/* Itemized list */}
            <div className="space-y-2 mb-4">
              <h4 className="font-bold text-xs text-gray-700 pb-1 border-b">قائمة الأصناف</h4>
              {invoiceOrder.items.map(i => (
                <div key={i.id} className="flex justify-between items-center text-xs py-1">
                  <div>
                    <span className="font-bold text-gray-800">{i.productNameAr}</span>
                    <span className="text-gray-400 mr-2 font-mono">×{i.quantity}</span>
                  </div>
                  <span className="font-bold text-gray-800">{(i.quantity * i.price).toFixed(2)} {t.sar}</span>
                </div>
              ))}
            </div>

            {/* Total breakdown */}
            <div className="space-y-2 p-3.5 bg-gray-50 rounded-2xl text-xs mb-6 border border-gray-200">
              <div className="flex justify-between text-gray-600">
                <span>المجموع الفرعي (غير شامل الضريبة)</span>
                <span className="font-bold">{invoiceOrder.subtotal.toFixed(2)} {t.sar}</span>
              </div>
              <div className="flex justify-between text-gray-600">
                <span>ضريبة القيمة المضافة (15% VAT)</span>
                <span className="font-bold">{invoiceOrder.vat15.toFixed(2)} {t.sar}</span>
              </div>
              <div className="flex justify-between text-gray-600">
                <span>رسوم التوصيل</span>
                <span className="font-bold">
                  {invoiceOrder.deliveryFee === 0 ? 'مجاني' : `${invoiceOrder.deliveryFee.toFixed(2)} ${t.sar}`}
                </span>
              </div>
              <div className="h-px bg-gray-200 my-1"></div>
              <div className="flex justify-between items-center text-sm font-black text-[#0b1c30]">
                <span>الإجمالي النهائي المستحق</span>
                <span className="text-base text-[#006948]">{invoiceOrder.total.toFixed(2)} {t.sar}</span>
              </div>
            </div>

            {/* Print & Action Buttons */}
            <div className="flex items-center gap-2 no-print">
              <button
                onClick={() => window.print()}
                className="flex-1 py-3 rounded-xl bg-[#006948] hover:bg-[#00855d] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xs transition-all active:scale-95"
              >
                <Printer className="w-4 h-4" />
                <span>{t.printInvoice}</span>
              </button>
              <button
                onClick={() => setIsInvoiceModalOpen(false)}
                className="px-4 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs"
              >
                إغلاق
              </button>
            </div>

          </div>
        </div>
      )}

    </div>
  );
};
