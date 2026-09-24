import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { translations } from '../../utils/translations';
import { Product } from '../../types';
import { 
  X, 
  Package, 
  Barcode, 
  Tag, 
  AlertTriangle, 
  CheckCircle2, 
  TrendingUp, 
  Globe,
  ShieldCheck,
  Edit3, 
  Clock, 
  Copy, 
  Check, 
  Plus, 
  Minus,
  Sparkles,
  DollarSign
} from 'lucide-react';

interface ProductDetailModalProps {
  product: Product | null;
  isOpen: boolean;
  onClose: () => void;
  onEdit: (product: Product) => void;
}

export const ProductDetailModal: React.FC<ProductDetailModalProps> = ({
  product,
  isOpen,
  onClose,
  onEdit
}) => {
  const { language, adjustStock, stockAdjustments } = useStore();
  const t = translations[language];
  const isRtl = language === 'ar';

  const [customRestockQty, setCustomRestockQty] = useState(10);
  const [copiedField, setCopiedField] = useState<string | null>(null);

  if (!isOpen || !product) return null;

  const isLow = product.stock > 0 && product.stock <= product.minStockAlert;
  const isOut = product.stock === 0;

  // Profit calculation
  const profitPerUnit = product.price - product.costPrice;
  const profitMarginPercent = product.price > 0 ? (profitPerUnit / product.price) * 100 : 0;
  const totalStockCost = product.stock * product.costPrice;
  const totalStockPotentialRevenue = product.stock * product.price;

  // Product specific stock adjustments
  const productLogs = stockAdjustments
    .filter(log => log.productId === product.id)
    .slice(0, 6);

  const copyToClipboard = (text: string, field: string) => {
    navigator.clipboard.writeText(text);
    setCopiedField(field);
    setTimeout(() => setCopiedField(null), 2000);
  };

  const handleQuickAdjust = (delta: number, note: string) => {
    adjustStock(product.id, delta, note);
  };

  return (
    <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto animate-in fade-in duration-200">
      <div 
        className="bg-white rounded-3xl max-w-2xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-[#e2e8f0] overflow-hidden my-auto"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Header */}
        <div className="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between bg-[#f8f9ff]">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-2xl bg-[#006948] text-white flex items-center justify-center shadow-xs">
              <Package className="w-5 h-5" />
            </div>
            <div>
              <div className="flex items-center gap-2">
                <h3 className="font-black text-base sm:text-lg text-[#0b1c30]">
                  {product.nameAr}
                </h3>
                <span className={`text-[10px] font-extrabold px-2 py-0.5 rounded-full ${
                  product.isActive ? 'bg-[#ecfdf5] text-[#006948]' : 'bg-gray-100 text-gray-600'
                }`}>
                  {product.isActive ? (isRtl ? 'نشط ومعروض' : 'Active') : (isRtl ? 'متوقف' : 'Inactive')}
                </span>
              </div>
              {product.nameEn && (
                <p className="text-xs text-gray-500 font-medium">{product.nameEn}</p>
              )}
            </div>
          </div>

          <button
            onClick={onClose}
            className="w-9 h-9 rounded-full bg-white hover:bg-gray-100 border border-gray-200 text-gray-500 flex items-center justify-center transition-colors shadow-2xs"
          >
            <X className="w-4 h-4" />
          </button>
        </div>

        {/* Scrollable Content */}
        <div className="p-4 sm:p-6 overflow-y-auto space-y-5 text-right" dir={isRtl ? 'rtl' : 'ltr'}>
          
          {/* Top Overview Bar */}
          <div className="flex flex-col sm:flex-row items-center gap-4 bg-[#f8f9ff] p-4 rounded-2xl border border-gray-100">
            <div className="w-24 h-24 rounded-2xl bg-white p-2 border border-gray-200 shrink-0 flex items-center justify-center shadow-2xs">
              <img 
                src={product.image} 
                alt={product.nameAr} 
                className="w-full h-full object-contain mix-blend-multiply"
              />
            </div>

            <div className="flex-1 min-w-0 space-y-2 w-full">
              <div className="flex items-center gap-2 flex-wrap">
                <span className="px-2.5 py-1 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700">
                  {product.category}
                </span>

                {product.countryOfOrigin && (
                  <span className="px-2.5 py-1 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-1.5">
                    <Globe className="w-3.5 h-3.5 text-[#006948]" />
                    <span>{isRtl ? 'بلد المنشأ: ' : 'Origin: '}{product.countryOfOrigin}</span>
                  </span>
                )}

                {product.storageMethod && (
                  <span className="px-2.5 py-1 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-medium flex items-center gap-1.5">
                    <ShieldCheck className="w-3.5 h-3.5 text-amber-600" />
                    <span>{isRtl ? 'الحفظ: ' : 'Storage: '}{product.storageMethod}</span>
                  </span>
                )}
              </div>

              {product.descriptionAr && (
                <p className="text-xs text-gray-700 leading-relaxed bg-white/80 p-2.5 rounded-xl border border-gray-100">
                  <span className="font-bold text-[11px] text-gray-500 block mb-0.5">{isRtl ? 'الوصف بالعربية:' : 'Arabic Description:'}</span>
                  {product.descriptionAr}
                </p>
              )}

              {product.descriptionEn && (
                <p className="text-xs text-gray-700 leading-relaxed bg-white/80 p-2.5 rounded-xl border border-gray-100" dir="ltr">
                  <span className="font-bold text-[11px] text-gray-500 block mb-0.5 text-left">{isRtl ? 'الوصف بالإنجليزية:' : 'English Description:'}</span>
                  {product.descriptionEn}
                </p>
              )}
            </div>
          </div>

          {/* Stock Level & Restock Controller */}
          <div className="bg-white rounded-2xl border border-[#e2e8f0] p-4 space-y-3 shadow-xs">
            <div className="flex items-center justify-between">
              <h4 className="font-extrabold text-xs sm:text-sm text-[#0b1c30] flex items-center gap-1.5">
                <Package className="w-4 h-4 text-[#006948]" />
                <span>{isRtl ? 'حالة المخزون وإعادة التوريد الفوري' : 'Stock Level & Restock Controls'}</span>
              </h4>

              <div className="flex items-center gap-1.5">
                {isOut ? (
                  <span className="text-xs font-extrabold text-red-700 bg-red-50 border border-red-200 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                    <AlertTriangle className="w-3.5 h-3.5" />
                    <span>{isRtl ? 'نافد من المخزون' : 'Out of Stock'}</span>
                  </span>
                ) : isLow ? (
                  <span className="text-xs font-extrabold text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                    <AlertTriangle className="w-3.5 h-3.5" />
                    <span>{isRtl ? 'مخزون حرج وشيك النفاد' : 'Low Stock Alert'}</span>
                  </span>
                ) : (
                  <span className="text-xs font-extrabold text-emerald-800 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                    <CheckCircle2 className="w-3.5 h-3.5" />
                    <span>{isRtl ? 'مخزون متوفر ومستقر' : 'In Stock'}</span>
                  </span>
                )}
              </div>
            </div>

            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
              <div className="bg-[#f8f9ff] p-3 rounded-xl border border-gray-100">
                <span className="text-[11px] text-gray-500 font-semibold block">{isRtl ? 'الكمية المتوفرة' : 'Current Stock'}</span>
                <span className={`text-2xl font-black block mt-0.5 ${
                  isOut ? 'text-red-600' : isLow ? 'text-amber-600' : 'text-[#006948]'
                }`}>
                  {product.stock}
                </span>
                <span className="text-[10px] text-gray-400">{product.unitAr}</span>
              </div>

              <div className="bg-[#f8f9ff] p-3 rounded-xl border border-gray-100">
                <span className="text-[11px] text-gray-500 font-semibold block">{isRtl ? 'حد تنبيه النفاد' : 'Min Alert Level'}</span>
                <span className="text-2xl font-black text-gray-700 block mt-0.5">{product.minStockAlert}</span>
                <span className="text-[10px] text-gray-400">{product.unitAr}</span>
              </div>

              <div className="bg-[#f8f9ff] p-3 rounded-xl border border-gray-100">
                <span className="text-[11px] text-gray-500 font-semibold block">{isRtl ? 'المبيعات المسجلة' : 'Units Sold'}</span>
                <span className="text-2xl font-black text-blue-600 block mt-0.5">{product.salesCount || 0}</span>
                <span className="text-[10px] text-gray-400">{isRtl ? 'طلب ناجح' : 'orders'}</span>
              </div>

              <div className="bg-[#f8f9ff] p-3 rounded-xl border border-gray-100">
                <span className="text-[11px] text-gray-500 font-semibold block">{isRtl ? 'تقدير البقاء' : 'Est. Days Left'}</span>
                <span className="text-2xl font-black text-purple-600 block mt-0.5">
                  {product.stock === 0 ? '0' : Math.max(1, Math.round(product.stock / Math.max(1, (product.salesCount || 1) / 7)))}
                </span>
                <span className="text-[10px] text-gray-400">{isRtl ? 'أيام عمل' : 'days'}</span>
              </div>
            </div>

            {/* Quick adjust buttons */}
            <div className="pt-2 border-t border-gray-100 flex items-center justify-between gap-2 flex-wrap">
              <span className="text-xs font-bold text-gray-600">{isRtl ? 'إجراء تعديل سريع على الرصيد:' : 'Quick stock adjustments:'}</span>
              <div className="flex items-center gap-1.5">
                <button
                  onClick={() => handleQuickAdjust(-1, 'إنقاص يدوي -1')}
                  disabled={product.stock <= 0}
                  className="px-2.5 py-1 rounded-xl bg-gray-100 hover:bg-red-50 text-gray-700 hover:text-red-700 text-xs font-bold transition-all disabled:opacity-40"
                >
                  -1
                </button>
                <button
                  onClick={() => handleQuickAdjust(+1, 'إضافة يدوية +1')}
                  className="px-2.5 py-1 rounded-xl bg-[#ecfdf5] hover:bg-[#85f8c4] text-[#006948] text-xs font-bold transition-all"
                >
                  +1
                </button>
                <button
                  onClick={() => handleQuickAdjust(+5, 'توريد دفعة +5')}
                  className="px-2.5 py-1 rounded-xl bg-[#ecfdf5] hover:bg-[#85f8c4] text-[#006948] text-xs font-bold transition-all"
                >
                  +5
                </button>
                <button
                  onClick={() => handleQuickAdjust(+10, 'توريد دفعة +10')}
                  className="px-2.5 py-1 rounded-xl bg-[#006948] hover:bg-[#00855d] text-white text-xs font-bold transition-all shadow-2xs"
                >
                  +10
                </button>
                <button
                  onClick={() => handleQuickAdjust(+25, 'شحنة مورد +25')}
                  className="px-2.5 py-1 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-2xs"
                >
                  +25
                </button>
              </div>
            </div>
          </div>

          {/* Pricing & Profit Margin Analysis */}
          <div className="bg-white rounded-2xl border border-[#e2e8f0] p-4 space-y-3 shadow-xs">
            <h4 className="font-extrabold text-xs sm:text-sm text-[#0b1c30] flex items-center gap-1.5">
              <TrendingUp className="w-4 h-4 text-emerald-600" />
              <span>{isRtl ? 'الأسعار وهوامش الربح' : 'Pricing & Margin Breakdown'}</span>
            </h4>

            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
              <div className="p-3 rounded-xl bg-[#ecfdf5] border border-[#006948]/20">
                <span className="text-[11px] text-emerald-800 font-semibold block">{isRtl ? 'سعر البيع للزبون' : 'Selling Price'}</span>
                <span className="text-xl font-black text-[#006948] mt-1 block">
                  {product.price.toFixed(2)} <span className="text-xs font-normal">{t.sar}</span>
                </span>
              </div>

              <div className="p-3 rounded-xl bg-gray-50 border border-gray-200">
                <span className="text-[11px] text-gray-500 font-semibold block">{isRtl ? 'تكلفة التوريد (الجملة)' : 'Cost Price'}</span>
                <span className="text-xl font-black text-gray-700 mt-1 block">
                  {product.costPrice.toFixed(2)} <span className="text-xs font-normal">{t.sar}</span>
                </span>
              </div>

              <div className="p-3 rounded-xl bg-blue-50 border border-blue-200">
                <span className="text-[11px] text-blue-700 font-semibold block">{isRtl ? 'صافي الربح للقطعة' : 'Profit per Unit'}</span>
                <span className="text-xl font-black text-blue-700 mt-1 block">
                  +{profitPerUnit.toFixed(2)} <span className="text-xs font-normal">{t.sar}</span>
                </span>
              </div>

              <div className="p-3 rounded-xl bg-amber-50 border border-amber-200">
                <span className="text-[11px] text-amber-800 font-semibold block">{isRtl ? 'نسبة هامش الربح' : 'Margin Rate'}</span>
                <span className="text-xl font-black text-amber-700 mt-1 block">
                  {profitMarginPercent.toFixed(1)}%
                </span>
              </div>
            </div>

            <div className="p-2.5 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-between text-xs text-gray-600">
              <span>{isRtl ? 'إجمالي القيمة المقدرة لمخزون هذا الصنف:' : 'Total inventory stock value:'}</span>
              <span className="font-extrabold text-[#0b1c30]">
                {totalStockCost.toFixed(2)} {t.sar} <span className="text-gray-400 font-normal">({isRtl ? 'تكلفة' : 'cost'})</span> • {totalStockPotentialRevenue.toFixed(2)} {t.sar} <span className="text-gray-400 font-normal">({isRtl ? 'مبيعات' : 'retail'})</span>
              </span>
            </div>
          </div>

          {/* Barcode & SKU */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div className="bg-[#f8f9ff] p-3.5 rounded-2xl border border-gray-200 flex items-center justify-between">
              <div>
                <span className="text-[10px] text-gray-400 font-semibold block uppercase">SKU (رمز الصنف)</span>
                <span className="font-mono font-bold text-sm text-[#0b1c30] mt-0.5 block">{product.sku}</span>
              </div>
              <button
                onClick={() => copyToClipboard(product.sku, 'sku')}
                className="p-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-[#006948] transition-colors shadow-2xs"
                title={isRtl ? 'نسخ' : 'Copy'}
              >
                {copiedField === 'sku' ? <Check className="w-4 h-4 text-emerald-600" /> : <Copy className="w-4 h-4" />}
              </button>
            </div>

            <div className="bg-[#f8f9ff] p-3.5 rounded-2xl border border-gray-200 flex items-center justify-between">
              <div>
                <span className="text-[10px] text-gray-400 font-semibold block uppercase">الباركود الدولي (Barcode)</span>
                <span className="font-mono font-bold text-sm text-[#0b1c30] mt-0.5 block">{product.barcode || '628100293812'}</span>
              </div>
              <button
                onClick={() => copyToClipboard(product.barcode || '628100293812', 'barcode')}
                className="p-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-[#006948] transition-colors shadow-2xs"
                title={isRtl ? 'نسخ' : 'Copy'}
              >
                {copiedField === 'barcode' ? <Check className="w-4 h-4 text-emerald-600" /> : <Copy className="w-4 h-4" />}
              </button>
            </div>
          </div>

          {/* Origin & Storage Information */}
          {(product.countryOfOrigin || product.storageMethod) && (
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div className="bg-[#f8f9ff] p-3.5 rounded-2xl border border-gray-200">
                <span className="text-[10px] text-gray-400 font-semibold block uppercase">
                  {isRtl ? 'بلد المنشأ' : 'Country of Origin'}
                </span>
                <div className="flex items-center gap-2 mt-1">
                  <Globe className="w-4 h-4 text-[#006948]" />
                  <span className="font-bold text-xs sm:text-sm text-[#0b1c30]">
                    {product.countryOfOrigin || (isRtl ? 'المملكة العربية السعودية' : 'Saudi Arabia')}
                  </span>
                </div>
              </div>

              <div className="bg-[#f8f9ff] p-3.5 rounded-2xl border border-gray-200">
                <span className="text-[10px] text-gray-400 font-semibold block uppercase">
                  {isRtl ? 'طريقة الحفظ والتخزين' : 'Storage & Preservation'}
                </span>
                <div className="flex items-center gap-2 mt-1">
                  <ShieldCheck className="w-4 h-4 text-amber-600 shrink-0" />
                  <span className="font-medium text-xs text-gray-800 leading-snug">
                    {product.storageMethod || (isRtl ? 'يحفظ في مكان جاف وبارد' : 'Keep in a cool, dry place')}
                  </span>
                </div>
              </div>
            </div>
          )}

          {/* Stock Adjustment History */}
          <div className="bg-white rounded-2xl border border-[#e2e8f0] p-4 space-y-2.5 shadow-xs">
            <h4 className="font-extrabold text-xs sm:text-sm text-[#0b1c30] flex items-center gap-1.5">
              <Clock className="w-4 h-4 text-gray-500" />
              <span>{isRtl ? 'سجل حركات وتوريد الصنف الأخيرة' : 'Stock Audit Trail'}</span>
            </h4>

            {productLogs.length === 0 ? (
              <p className="text-xs text-gray-400 py-3 text-center">
                {isRtl ? 'لم تسجل حركات مخزون يدوية لهذا الصنف بعد. يمكنك استخدام أزرار التوريد أعلاه.' : 'No stock adjustments recorded yet for this item.'}
              </p>
            ) : (
              <div className="divide-y divide-gray-100 text-xs">
                {productLogs.map(log => (
                  <div key={log.id} className="py-2 flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <span className={`w-2 h-2 rounded-full ${log.quantity > 0 ? 'bg-emerald-500' : 'bg-red-500'}`} />
                      <span className="font-medium text-gray-700">{log.reason || (log.quantity > 0 ? 'توريد' : 'بيع')}</span>
                      <span className="text-[11px] text-gray-400">({new Date(log.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })})</span>
                    </div>

                    <div className="flex items-center gap-2 font-mono">
                      <span className={log.quantity > 0 ? 'text-emerald-700 font-bold' : 'text-red-600 font-bold'}>
                        {log.quantity > 0 ? `+${log.quantity}` : log.quantity}
                      </span>
                      <span className="text-gray-400">→</span>
                      <span className="font-bold text-gray-800">{log.newStock} {product.unitAr}</span>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>

        </div>

        {/* Footer Actions */}
        <div className="p-4 border-t border-gray-100 bg-[#f8f9ff] flex items-center justify-between">
          <button
            onClick={() => {
              onClose();
              onEdit(product);
            }}
            className="px-4 py-2.5 rounded-xl bg-[#006948] hover:bg-[#00855d] text-white text-xs font-bold flex items-center gap-1.5 shadow-xs active:scale-95 transition-all"
          >
            <Edit3 className="w-4 h-4" />
            <span>{t.editProduct}</span>
          </button>

          <button
            onClick={onClose}
            className="px-5 py-2.5 rounded-xl bg-white border border-gray-300 text-gray-700 text-xs font-bold hover:bg-gray-50 transition-colors shadow-2xs"
          >
            {isRtl ? 'إغلاق' : 'Close'}
          </button>
        </div>
      </div>
    </div>
  );
};
