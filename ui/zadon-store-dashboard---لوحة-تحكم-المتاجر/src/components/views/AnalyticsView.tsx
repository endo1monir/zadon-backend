import React, { useMemo } from 'react';
import { useStore } from '../../context/StoreContext';
import { translations } from '../../utils/translations';
import { 
  BarChart, 
  Bar, 
  AreaChart, 
  Area, 
  XAxis, 
  YAxis, 
  Tooltip, 
  ResponsiveContainer, 
  PieChart, 
  Pie, 
  Cell, 
  CartesianGrid 
} from 'recharts';
import { 
  BarChart3, 
  TrendingUp, 
  AlertTriangle, 
  Package, 
  Clock, 
  CheckCircle2, 
  RotateCw, 
  Zap,
  ArrowUpRight,
  ShieldCheck,
  Plus
} from 'lucide-react';

export const AnalyticsView: React.FC = () => {
  const { currentStore, products, orders, language, adjustStock } = useStore();
  const t = translations[language];

  // 1. Stock Valuation & Inventory Health
  const totalInventoryValue = useMemo(() => {
    return products.reduce((sum, p) => sum + (p.stock * p.price), 0);
  }, [products]);

  const totalCostValue = useMemo(() => {
    return products.reduce((sum, p) => sum + (p.stock * p.costPrice), 0);
  }, [products]);

  const potentialProfit = Math.max(0, totalInventoryValue - totalCostValue);

  // Out of stock rate
  const outOfStockRate = useMemo(() => {
    if (products.length === 0) return 0;
    const outCount = products.filter(p => p.stock === 0).length;
    return Math.round((outCount / products.length) * 100);
  }, [products]);

  // 2. Hourly Sales Activity Data (Simulated smooth real-time curve for today)
  const hourlyData = useMemo(() => {
    return [
      { time: '08:00', sales: 45, orders: 2 },
      { time: '10:00', sales: 120, orders: 5 },
      { time: '12:00', sales: 240, orders: 9 },
      { time: '14:00', sales: 380, orders: 14 },
      { time: '16:00', sales: 490, orders: 18 },
      { time: '18:00', sales: 720, orders: 26 },
      { time: '20:00', sales: 940, orders: 34 },
      { time: '22:00', sales: 1150, orders: 41 },
    ];
  }, []);

  // 3. Stock by Category
  const categoryStockData = useMemo(() => {
    const map: Record<string, { totalStock: number; count: number }> = {};
    products.forEach(p => {
      const cat = p.category || 'عام';
      if (!map[cat]) map[cat] = { totalStock: 0, count: 0 };
      map[cat].totalStock += p.stock;
      map[cat].count += 1;
    });

    return Object.entries(map).map(([category, data]) => ({
      category,
      stock: data.totalStock,
      productsCount: data.count
    }));
  }, [products]);

  // 4. Order Status Distribution for Donut Chart
  const statusPieData = useMemo(() => {
    const counts: Record<string, number> = {
      'جديد': orders.filter(o => o.status === 'new').length,
      'قيد التجهيز': orders.filter(o => o.status === 'preparing').length,
      'جاهز للاستلام': orders.filter(o => o.status === 'ready_for_pickup').length,
      'في الطريق': orders.filter(o => o.status === 'out_for_delivery').length,
      'تم التسليم': orders.filter(o => o.status === 'delivered').length,
    };

    const colors = ['#f59e0b', '#3b82f6', '#8b5cf6', '#10b981', '#6b7280'];

    return Object.entries(counts)
      .filter(([_, value]) => value > 0)
      .map(([name, value], idx) => ({
        name,
        value,
        color: colors[idx % colors.length]
      }));
  }, [orders]);

  // 5. Stock Depletion & Projected Days to Stockout Forecasting
  // Based on current stock vs sales rate
  const depletionForecast = useMemo(() => {
    return products.map(p => {
      // Estimated daily run rate
      const dailyVelocity = Math.max(1, Math.round((p.salesCount / 14) + (p.stock <= p.minStockAlert ? 2 : 1)));
      const daysLeft = p.stock === 0 ? 0 : Math.round(p.stock / dailyVelocity);

      let urgency: 'critical' | 'warning' | 'healthy' = 'healthy';
      if (daysLeft <= 1 || p.stock === 0) urgency = 'critical';
      else if (daysLeft <= 4) urgency = 'warning';

      return {
        ...p,
        dailyVelocity,
        daysLeft,
        urgency
      };
    }).sort((a, b) => a.daysLeft - b.daysLeft);
  }, [products]);

  return (
    <div className="space-y-6">
      
      {/* Header */}
      <div>
        <h2 className="text-xl sm:text-2xl font-black text-[#0b1c30] tracking-tight flex items-center gap-2">
          <BarChart3 className="w-6 h-6 text-[#006948]" />
          <span>{t.realTimeAnalytics}</span>
        </h2>
        <p className="text-xs text-gray-500 mt-1">
          {language === 'ar'
            ? 'مؤشرات دقيقة لتنبؤ نفاد المخزون، سرعة دوران الأصناف، وتقدير الاحتياج لإعادة الطلب.'
            : 'Inventory forecasting, stock turnover speed, and replenishment triggers.'}
        </p>
      </div>

      {/* Top 4 KPI Metrics */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        
        <div className="bg-white p-4 rounded-2xl border border-[#e2e8f0] shadow-xs">
          <div className="flex items-center justify-between text-gray-500 mb-2">
            <span className="text-xs font-bold text-[#3d4a42]">قيمة المخزون الإجمالية</span>
            <div className="w-8 h-8 rounded-lg bg-[#ecfdf5] text-[#006948] flex items-center justify-center">
              <Package className="w-4 h-4" />
            </div>
          </div>
          <div className="flex items-baseline gap-1">
            <span className="text-2xl font-black text-[#0b1c30]">
              {totalInventoryValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
            </span>
            <span className="text-xs font-bold text-[#006948]">{t.sar}</span>
          </div>
          <span className="text-[11px] text-gray-400 block mt-1">
            هامش الربح المتوقع: +{potentialProfit.toFixed(0)} {t.sar}
          </span>
        </div>

        <div className="bg-white p-4 rounded-2xl border border-[#e2e8f0] shadow-xs">
          <div className="flex items-center justify-between text-gray-500 mb-2">
            <span className="text-xs font-bold text-[#3d4a42]">معدل نفاد المخزون</span>
            <div className={`w-8 h-8 rounded-lg flex items-center justify-center ${
              outOfStockRate > 0 ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600'
            }`}>
              <AlertTriangle className="w-4 h-4" />
            </div>
          </div>
          <div className="flex items-baseline gap-2">
            <span className={`text-2xl font-black ${outOfStockRate > 0 ? 'text-red-600' : 'text-emerald-600'}`}>
              {outOfStockRate}%
            </span>
            <span className="text-xs text-gray-500">
              ({products.filter(p => p.stock === 0).length} من {products.length} أصناف)
            </span>
          </div>
          <span className="text-[11px] text-gray-400 block mt-1">
            {outOfStockRate === 0 ? 'لا توجد انقطاعات في الإمداد' : 'يؤثر سلباً على قبول الطلبات السريعة'}
          </span>
        </div>

        <div className="bg-white p-4 rounded-2xl border border-[#e2e8f0] shadow-xs">
          <div className="flex items-center justify-between text-gray-500 mb-2">
            <span className="text-xs font-bold text-[#3d4a42]">متوسط سرعة التجهيز</span>
            <div className="w-8 h-8 rounded-lg bg-[#eff4ff] text-[#006194] flex items-center justify-center">
              <Clock className="w-4 h-4" />
            </div>
          </div>
          <div className="flex items-baseline gap-1">
            <span className="text-2xl font-black text-[#0b1c30]">14.2</span>
            <span className="text-xs font-bold text-[#006194]">{t.mins}</span>
          </div>
          <span className="text-[11px] text-emerald-600 font-bold block mt-1">
            ✓ أسرع بـ 12% من متوسط المتاجر
          </span>
        </div>

        <div className="bg-white p-4 rounded-2xl border border-[#e2e8f0] shadow-xs">
          <div className="flex items-center justify-between text-gray-500 mb-2">
            <span className="text-xs font-bold text-[#3d4a42]">معدل دوران المخزون</span>
            <div className="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
              <RotateCw className="w-4 h-4" />
            </div>
          </div>
          <div className="flex items-baseline gap-1">
            <span className="text-2xl font-black text-[#0b1c30]">4.8x</span>
            <span className="text-xs text-gray-400">سنوياً</span>
          </div>
          <span className="text-[11px] text-gray-400 block mt-1">
            دوران نشط ومثالي للتجارة السريعة
          </span>
        </div>

      </div>

      {/* Visual Analytics Charts: Area & Bar */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {/* Hourly Sales Chart */}
        <div className="bg-white rounded-3xl border border-[#e2e8f0] p-5 shadow-xs">
          <div className="flex items-center justify-between mb-4">
            <div>
              <h3 className="font-bold text-sm text-[#0b1c30]">{t.hourlySales}</h3>
              <p className="text-xs text-gray-400">حركة المبيعات والطلبات المتزامنة خلال اليوم</p>
            </div>
            <span className="text-xs font-bold text-[#006948] bg-[#ecfdf5] px-2.5 py-1 rounded-full">
              مباشر 🟢
            </span>
          </div>

          <div className="h-64 w-full">
            <ResponsiveContainer width="100%" height="100%">
              <AreaChart data={hourlyData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                <defs>
                  <linearGradient id="colorSales" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="#006948" stopOpacity={0.4}/>
                    <stop offset="95%" stopColor="#006948" stopOpacity={0}/>
                  </linearGradient>
                </defs>
                <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9" />
                <XAxis dataKey="time" stroke="#94a3b8" fontSize={11} tickLine={false} />
                <YAxis stroke="#94a3b8" fontSize={11} tickLine={false} />
                <Tooltip 
                  contentStyle={{ backgroundColor: '#ffffff', borderRadius: '12px', border: '1px solid #e2e8f0', boxShadow: '0 4px 12px rgba(0,0,0,0.05)' }}
                  formatter={(value: any) => [`${value} ${t.sar}`, 'المبيعات']}
                />
                <Area type="monotone" dataKey="sales" stroke="#006948" strokeWidth={2.5} fillOpacity={1} fill="url(#colorSales)" />
              </AreaChart>
            </ResponsiveContainer>
          </div>
        </div>

        {/* Category Stock Distribution Bar Chart */}
        <div className="bg-white rounded-3xl border border-[#e2e8f0] p-5 shadow-xs">
          <div className="flex items-center justify-between mb-4">
            <div>
              <h3 className="font-bold text-sm text-[#0b1c30]">{t.stockDistribution}</h3>
              <p className="text-xs text-gray-400">إجمالي قطع المخزون المتاحة لكل تصنيف</p>
            </div>
            <span className="text-xs text-gray-500 font-mono">
              {products.reduce((acc, p) => acc + p.stock, 0)} قطعة
            </span>
          </div>

          <div className="h-64 w-full">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={categoryStockData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9" />
                <XAxis dataKey="category" stroke="#94a3b8" fontSize={11} tickLine={false} />
                <YAxis stroke="#94a3b8" fontSize={11} tickLine={false} />
                <Tooltip 
                  contentStyle={{ backgroundColor: '#ffffff', borderRadius: '12px', border: '1px solid #e2e8f0' }}
                  formatter={(value: any) => [`${value} قطعة`, 'كمية المخزون']}
                />
                <Bar dataKey="stock" fill="#00855d" radius={[8, 8, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>

      </div>

      {/* Stockout Risk Forecasting Table (Smart Inventory Management) */}
      <div className="bg-white rounded-3xl border border-[#e2e8f0] p-5 shadow-xs">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-4 border-b border-gray-100">
          <div>
            <h3 className="font-extrabold text-base text-[#0b1c30] flex items-center gap-2">
              <Zap className="w-5 h-5 text-amber-500" />
              <span>{t.depletionForecast}</span>
            </h3>
            <p className="text-xs text-gray-500 mt-0.5">
              حساب ذكي لمعدل الاستهلاك اليومي والتنبؤ بالأيام المتبقية قبل نفاد الصنف لتفادي خسارة المبيعات.
            </p>
          </div>
          <span className="text-xs text-gray-400 font-medium">
            مرتبة حسب الأولوية وحجم الخطر
          </span>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-right text-xs">
            <thead>
              <tr className="border-b border-gray-100 text-gray-400 font-bold pb-2">
                <th className="py-2.5 px-3">المنتج</th>
                <th className="py-2.5 px-3">المخزون الحالي</th>
                <th className="py-2.5 px-3">الاستهلاك المتوقع / يوم</th>
                <th className="py-2.5 px-3">الوقت المتبقي للنفاد</th>
                <th className="py-2.5 px-3">مستوى الخطورة</th>
                <th className="py-2.5 px-3 text-center">إجراء إعادة الطلب</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-50">
              {depletionForecast.map(prod => (
                <tr key={prod.id} className="hover:bg-[#f8f9ff] transition-colors">
                  <td className="py-3 px-3">
                    <div className="flex items-center gap-2.5">
                      <img src={prod.image} alt={prod.nameAr} className="w-9 h-9 rounded-lg object-contain bg-white border" />
                      <div>
                        <span className="font-bold text-gray-900 block">{prod.nameAr}</span>
                        <span className="text-[10px] text-gray-400">{prod.sku} • {prod.category}</span>
                      </div>
                    </div>
                  </td>
                  <td className="py-3 px-3 font-bold text-gray-800">
                    {prod.stock} {prod.unitAr}
                  </td>
                  <td className="py-3 px-3 text-gray-600 font-mono">
                    ~{prod.dailyVelocity} {prod.unitAr}/يوم
                  </td>
                  <td className="py-3 px-3">
                    <span className={`font-black ${
                      prod.daysLeft === 0 ? 'text-red-600' : prod.daysLeft <= 2 ? 'text-amber-600' : 'text-emerald-700'
                    }`}>
                      {prod.daysLeft === 0 ? 'نافد حالياً' : `${prod.daysLeft} يوم`}
                    </span>
                  </td>
                  <td className="py-3 px-3">
                    {prod.urgency === 'critical' ? (
                      <span className="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-800 flex items-center gap-1 w-fit">
                        <AlertTriangle className="w-3 h-3" />
                        حرج جداً
                      </span>
                    ) : prod.urgency === 'warning' ? (
                      <span className="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 flex items-center gap-1 w-fit">
                        <AlertTriangle className="w-3 h-3" />
                        تحذير نقص
                      </span>
                    ) : (
                      <span className="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1 w-fit">
                        <CheckCircle2 className="w-3 h-3" />
                        مخزون آمن
                      </span>
                    )}
                  </td>
                  <td className="py-3 px-3 text-center">
                    <button
                      onClick={() => adjustStock(prod.id, 25, 'طلب توريد من شاشة توقع النفاد')}
                      className="px-3 py-1.5 rounded-xl bg-[#ecfdf5] hover:bg-[#85f8c4] text-[#006948] font-bold text-xs shadow-2xs active:scale-95 transition-all inline-flex items-center gap-1"
                    >
                      <Plus className="w-3.5 h-3.5" />
                      <span>توريد +25</span>
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

    </div>
  );
};
