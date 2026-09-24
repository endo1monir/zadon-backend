import React, { useState, useMemo, useRef } from 'react';
import { useStore } from '../../context/StoreContext';
import { translations } from '../../utils/translations';
import { Product } from '../../types';
import { 
  Plus, 
  Search, 
  Filter, 
  AlertTriangle, 
  CheckCircle2, 
  XCircle, 
  Edit3, 
  Trash2, 
  FileText, 
  Package, 
  ArrowUpDown, 
  Download, 
  Copy,
  Eye,
  Globe,
  ShieldCheck,
  UploadCloud,
  ImageIcon
} from 'lucide-react';
import { ProductDetailModal } from '../modals/ProductDetailModal';

interface ProductsViewProps {
  isAddModalOpen: boolean;
  setIsAddModalOpen: (open: boolean) => void;
  selectedProductId?: string | null;
  onSelectProduct?: (productId: string | null) => void;
}

const DEFAULT_PRODUCT_IMAGE = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=500&auto=format&fit=crop&q=80';

export const ProductsView: React.FC<ProductsViewProps> = ({ 
  isAddModalOpen, 
  setIsAddModalOpen,
  selectedProductId,
  onSelectProduct 
}) => {
  const { 
    currentStore, 
    products, 
    language, 
    addProduct, 
    updateProduct, 
    deleteProduct, 
    adjustStock 
  } = useStore();

  const t = translations[language];

  // Inspect Product Modal State
  const [inspectingProduct, setInspectingProduct] = useState<Product | null>(null);

  // Sync with selectedProductId from navigation
  React.useEffect(() => {
    if (selectedProductId) {
      const found = products.find(p => p.id === selectedProductId);
      if (found) setInspectingProduct(found);
    }
  }, [selectedProductId, products]);

  // Filters & Search
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [stockStatusFilter, setStockStatusFilter] = useState<'all' | 'low' | 'out'>('all');

  // Edit Product State
  const [editingProduct, setEditingProduct] = useState<Product | null>(null);
  const [deletingProductId, setDeletingProductId] = useState<string | null>(null);

  // File Upload & Drag-and-drop state
  const [isDragging, setIsDragging] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleFileUpload = (file: File) => {
    if (!file || !file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = (e) => {
      if (e.target?.result) {
        setFormData(prev => ({ ...prev, image: e.target!.result as string }));
      }
    };
    reader.readAsDataURL(file);
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      handleFileUpload(e.dataTransfer.files[0]);
    }
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      handleFileUpload(e.target.files[0]);
    }
  };

  // Form State
  const [formData, setFormData] = useState({
    nameAr: '',
    nameEn: '',
    category: '',
    sku: '',
    barcode: '',
    price: 10,
    originalPrice: 0,
    costPrice: 6,
    stock: 20,
    minStockAlert: 10,
    unitAr: 'قطعة',
    unitEn: 'piece',
    image: '',
    countryOfOrigin: 'المملكة العربية السعودية',
    storageMethod: 'يحفظ في مكان جاف وبارد',
    isActive: true,
    descriptionAr: '',
    descriptionEn: ''
  });

  // Unique categories list for tabs
  const categories = useMemo(() => {
    const set = new Set<string>();
    products.forEach(p => {
      if (p.category) set.add(p.category);
    });
    return Array.from(set);
  }, [products]);

  // Filtered Products
  const filteredProducts = useMemo(() => {
    return products.filter(prod => {
      // Search
      const matchesSearch = 
        prod.nameAr.toLowerCase().includes(searchQuery.toLowerCase()) ||
        prod.nameEn.toLowerCase().includes(searchQuery.toLowerCase()) ||
        prod.sku.toLowerCase().includes(searchQuery.toLowerCase());

      // Category
      const matchesCategory = selectedCategory === 'all' || prod.category === selectedCategory;

      // Stock status
      let matchesStock = true;
      if (stockStatusFilter === 'low') {
        matchesStock = prod.stock > 0 && prod.stock <= prod.minStockAlert;
      } else if (stockStatusFilter === 'out') {
        matchesStock = prod.stock === 0;
      }

      return matchesSearch && matchesCategory && matchesStock;
    });
  }, [products, searchQuery, selectedCategory, stockStatusFilter]);

  // Open modal for new product
  const handleOpenAdd = () => {
    setEditingProduct(null);
    setFormData({
      nameAr: '',
      nameEn: '',
      category: categories[0] || 'عام',
      sku: `SKU-${Math.floor(1000 + Math.random() * 9000)}`,
      barcode: `628${Math.floor(1000000000 + Math.random() * 9000000000)}`,
      price: 15.00,
      originalPrice: 0,
      costPrice: 10.00,
      stock: 25,
      minStockAlert: 10,
      unitAr: 'عبوة',
      unitEn: 'pack',
      image: '',
      countryOfOrigin: 'المملكة العربية السعودية',
      storageMethod: 'يحفظ في مكان جاف وبارد بعيداً عن أشعة الشمس',
      isActive: true,
      descriptionAr: '',
      descriptionEn: ''
    });
    setIsAddModalOpen(true);
  };

  // Open modal for editing existing product
  const handleOpenEdit = (prod: Product) => {
    setEditingProduct(prod);
    setFormData({
      nameAr: prod.nameAr,
      nameEn: prod.nameEn,
      category: prod.category,
      sku: prod.sku,
      barcode: prod.barcode || '',
      price: prod.price,
      originalPrice: prod.originalPrice || 0,
      costPrice: prod.costPrice,
      stock: prod.stock,
      minStockAlert: prod.minStockAlert,
      unitAr: prod.unitAr,
      unitEn: prod.unitEn,
      image: prod.image || '',
      countryOfOrigin: prod.countryOfOrigin || 'المملكة العربية السعودية',
      storageMethod: prod.storageMethod || 'يحفظ في مكان جاف وبارد',
      isActive: prod.isActive,
      descriptionAr: prod.descriptionAr || '',
      descriptionEn: prod.descriptionEn || ''
    });
    setIsAddModalOpen(true);
  };

  // Handle Save
  const handleFormSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.nameAr.trim()) return;

    const finalImage = formData.image.trim() || DEFAULT_PRODUCT_IMAGE;

    if (editingProduct) {
      updateProduct(editingProduct.id, {
        ...formData,
        image: finalImage,
        originalPrice: formData.originalPrice > 0 ? formData.originalPrice : undefined
      });
    } else {
      addProduct({
        ...formData,
        image: finalImage,
        originalPrice: formData.originalPrice > 0 ? formData.originalPrice : undefined
      });
    }

    setIsAddModalOpen(false);
    setEditingProduct(null);
  };

  // CSV Export helper
  const handleExportCSV = () => {
    const headers = ['ID', 'Name AR', 'Name EN', 'Category', 'SKU', 'Price (SAR)', 'Cost (SAR)', 'Stock', 'Min Alert', 'Origin', 'Storage'];
    const rows = products.map(p => [
      p.id,
      `"${p.nameAr}"`,
      `"${p.nameEn}"`,
      `"${p.category}"`,
      p.sku,
      p.price,
      p.costPrice,
      p.stock,
      p.minStockAlert,
      `"${p.countryOfOrigin || ''}"`,
      `"${p.storageMethod || ''}"`
    ]);

    const csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `inventory_${currentStore?.id || 'store'}_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  return (
    <div className="space-y-6">
      
      {/* Top Header & Action */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-xl sm:text-2xl font-black text-[#0b1c30] tracking-tight flex items-center gap-2">
            <Package className="w-6 h-6 text-[#006948]" />
            <span>{t.products}</span>
            <span className="text-xs font-bold px-2.5 py-1 rounded-full bg-[#ecfdf5] text-[#006948]">
              {products.length} {language === 'ar' ? 'صنف مسجل' : 'SKUs'}
            </span>
          </h2>
          <p className="text-xs text-gray-500 mt-1">
            {language === 'ar' ? 'إضافة وتعديل بيانات المنتجات، مراقبة المخزون الفوري وتحديث الأسعار.' : 'Manage catalog, update stock and prices in real-time.'}
          </p>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={handleExportCSV}
            title="تصدير جدول المخزون كملف CSV"
            className="px-3.5 py-2.5 rounded-xl bg-white border border-[#e2e8f0] text-gray-700 text-xs font-bold hover:bg-[#f8f9ff] flex items-center gap-1.5 shadow-2xs active:scale-95 transition-all"
          >
            <Download className="w-4 h-4 text-gray-500" />
            <span className="hidden sm:inline">تصدير CSV</span>
          </button>
          <button
            onClick={handleOpenAdd}
            className="px-4 py-2.5 rounded-xl bg-[#006948] hover:bg-[#00855d] text-white text-xs font-bold flex items-center gap-1.5 shadow-xs active:scale-95 transition-all"
          >
            <Plus className="w-4 h-4" />
            <span>{t.addProduct}</span>
          </button>
        </div>
      </div>

      {/* Filter and Search Bar */}
      <div className="bg-white p-4 rounded-2xl border border-[#e2e8f0] shadow-xs space-y-3">
        <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
          
          {/* Search Field */}
          <div className="relative flex-1">
            <Search className="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder={t.searchProducts}
              className="w-full bg-[#f8f9ff] text-xs sm:text-sm text-[#0b1c30] placeholder-gray-400 pr-9 pl-3 py-2.5 rounded-xl border border-[#e2e8f0] focus:outline-none focus:border-[#006948] focus:bg-white transition-all"
            />
          </div>

          {/* Stock Status Pills Filter */}
          <div className="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
            <button
              onClick={() => setStockStatusFilter('all')}
              className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap ${
                stockStatusFilter === 'all'
                  ? 'bg-[#006948] text-white'
                  : 'bg-[#f8f9ff] text-gray-600 hover:bg-gray-100'
              }`}
            >
              {t.allItems}
            </button>
            <button
              onClick={() => setStockStatusFilter('low')}
              className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1 ${
                stockStatusFilter === 'low'
                  ? 'bg-amber-600 text-white'
                  : 'bg-amber-50 text-amber-700 hover:bg-amber-100'
              }`}
            >
              <AlertTriangle className="w-3.5 h-3.5" />
              <span>{t.lowStockOnly}</span>
            </button>
            <button
              onClick={() => setStockStatusFilter('out')}
              className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1 ${
                stockStatusFilter === 'out'
                  ? 'bg-red-600 text-white'
                  : 'bg-red-50 text-red-700 hover:bg-red-100'
              }`}
            >
              <XCircle className="w-3.5 h-3.5" />
              <span>{t.outOfStockOnly}</span>
            </button>
          </div>
        </div>

        {/* Categories Chips */}
        {categories.length > 0 && (
          <div className="flex items-center gap-1.5 overflow-x-auto pt-2 border-t border-gray-100 text-xs">
            <button
              onClick={() => setSelectedCategory('all')}
              className={`px-2.5 py-1 rounded-lg font-medium transition-colors ${
                selectedCategory === 'all' ? 'bg-[#eff4ff] text-[#006194] font-bold' : 'text-gray-500 hover:text-gray-800'
              }`}
            >
              {t.allCategories}
            </button>
            {categories.map(cat => (
              <button
                key={cat}
                onClick={() => setSelectedCategory(cat)}
                className={`px-2.5 py-1 rounded-lg font-medium transition-colors whitespace-nowrap ${
                  selectedCategory === cat ? 'bg-[#eff4ff] text-[#006194] font-bold' : 'text-gray-500 hover:text-gray-800'
                }`}
              >
                {cat}
              </button>
            ))}
          </div>
        )}
      </div>

      {/* Product Cards Table / Grid */}
      <div className="bg-white rounded-3xl border border-[#e2e8f0] shadow-xs overflow-hidden">
        {filteredProducts.length === 0 ? (
          <div className="py-16 text-center text-gray-400">
            <Package className="w-12 h-12 mx-auto mb-3 opacity-30" />
            <h4 className="text-base font-bold text-gray-700">لم يتم العثور على أي منتج</h4>
            <p className="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
              جرب تغيير كلمات البحث أو تصفية الحالة، أو أضف منتجاً جديداً إلى الكتالوج.
            </p>
            <button
              onClick={handleOpenAdd}
              className="mt-4 px-4 py-2 rounded-xl bg-[#006948] text-white text-xs font-bold shadow-xs hover:bg-[#00855d]"
            >
              {t.addProduct}
            </button>
          </div>
        ) : (
          <div className="divide-y divide-gray-100">
            {filteredProducts.map(product => {
              const isLow = product.stock > 0 && product.stock <= product.minStockAlert;
              const isOut = product.stock === 0;

              return (
                <div 
                  key={product.id}
                  className="p-4 hover:bg-[#f8f9ff] transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4"
                >
                  {/* Left: Image & Info */}
                  <div className="flex items-start gap-3.5 min-w-0 flex-1">
                    <div 
                      onClick={() => setInspectingProduct(product)}
                      className="relative w-16 h-16 rounded-xl bg-white border border-[#e2e8f0] hover:border-[#006948] overflow-hidden shrink-0 p-1 flex items-center justify-center cursor-pointer transition-colors shadow-2xs group"
                      title={t.viewDetails}
                    >
                      <img 
                        src={product.image} 
                        alt={product.nameAr}
                        className="w-full h-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform"
                      />
                    </div>

                    <div className="min-w-0 flex-1">
                      <div className="flex items-center gap-2 flex-wrap">
                        <h3 
                          onClick={() => setInspectingProduct(product)}
                          className="font-bold text-sm text-[#0b1c30] hover:text-[#006948] cursor-pointer truncate transition-colors"
                        >
                          {product.nameAr}
                        </h3>
                        {product.nameEn && (
                          <span className="text-xs text-gray-400 font-normal">
                            ({product.nameEn})
                          </span>
                        )}
                        <span className="px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 text-[10px] font-semibold">
                          {product.category}
                        </span>
                      </div>

                      {/* Specs and tags */}
                      <div className="flex items-center gap-2.5 mt-1.5 text-xs text-gray-500 flex-wrap">
                        <span className="font-mono text-[11px] bg-[#f8f9ff] px-1.5 py-0.5 rounded border border-gray-200">
                          {product.sku}
                        </span>
                        <span>• {product.unitAr}</span>
                        {product.countryOfOrigin && (
                          <span className="flex items-center gap-1 text-[11px] text-emerald-800 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">
                            <Globe className="w-3 h-3 text-[#006948]" />
                            <span>{product.countryOfOrigin}</span>
                          </span>
                        )}
                        {product.storageMethod && (
                          <span className="flex items-center gap-1 text-[11px] text-amber-900 bg-amber-50 border border-amber-200/60 px-1.5 py-0.5 rounded truncate max-w-[200px]" title={product.storageMethod}>
                            <ShieldCheck className="w-3 h-3 text-amber-600 shrink-0" />
                            <span className="truncate">{product.storageMethod}</span>
                          </span>
                        )}
                      </div>
                    </div>
                  </div>

                  {/* Middle: Pricing & Profit Margin */}
                  <div className="flex items-center justify-between md:justify-end gap-6 border-t md:border-t-0 pt-2 md:pt-0 border-gray-100">
                    <div className="text-right">
                      <span className="text-[10px] text-gray-400 block">{t.sellingPrice}</span>
                      <div className="flex items-baseline gap-1">
                        <span className="text-base font-black text-[#006948]">
                          {product.price.toFixed(2)}
                        </span>
                        <span className="text-[10px] font-bold text-gray-500">{t.sar}</span>
                      </div>
                      <span className="text-[10px] text-gray-400">
                        التكلفة: {product.costPrice.toFixed(2)} {t.sar}
                      </span>
                    </div>

                    {/* Stock Status & Inline Controls */}
                    <div className="flex flex-col items-end">
                      <span className="text-[10px] text-gray-400 mb-1">{t.stockQuantity}</span>
                      
                      <div className="flex items-center gap-2">
                        {/* Inline Stepper */}
                        <div className="flex items-center bg-[#f8f9ff] border border-gray-200 rounded-xl p-0.5 shadow-2xs">
                          <button
                            onClick={() => adjustStock(product.id, -1, 'إنقاص يدوي')}
                            disabled={product.stock <= 0}
                            className="w-7 h-7 rounded-lg flex items-center justify-center text-gray-600 hover:bg-white hover:text-red-600 disabled:opacity-30 active:scale-95 transition-all"
                          >
                            -
                          </button>
                          <span className={`px-2.5 font-black text-sm min-w-[32px] text-center ${
                            isOut ? 'text-red-600' : isLow ? 'text-amber-600' : 'text-[#0b1c30]'
                          }`}>
                            {product.stock}
                          </span>
                          <button
                            onClick={() => adjustStock(product.id, +1, 'زيادة يدوية')}
                            className="w-7 h-7 rounded-lg flex items-center justify-center text-gray-600 hover:bg-white hover:text-[#006948] active:scale-95 transition-all"
                          >
                            +
                          </button>
                        </div>

                        {/* Quick +10 Restock Pill */}
                        <button
                          onClick={() => adjustStock(product.id, +10, 'توريد دفعة سريعة')}
                          title="إضافة 10 قطع"
                          className="px-2 py-1.5 rounded-xl bg-[#ecfdf5] hover:bg-[#85f8c4] text-[#006948] font-bold text-xs shadow-2xs active:scale-95 transition-all"
                        >
                          +10
                        </button>
                      </div>

                      {/* Status indicator note */}
                      <div className="mt-1">
                        {isOut ? (
                          <span className="text-[10px] font-bold text-red-600 bg-red-50 px-1.5 py-0.5 rounded">
                            {t.outOfStock}
                          </span>
                        ) : isLow ? (
                          <span className="text-[10px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded">
                            {t.lowStockWarning} (&lt; {product.minStockAlert})
                          </span>
                        ) : (
                          <span className="text-[10px] font-semibold text-emerald-700">
                            مخزون كافٍ
                          </span>
                        )}
                      </div>
                    </div>

                    {/* Edit / Delete / Inspect actions */}
                    <div className="flex items-center gap-1">
                      <button
                        onClick={() => setInspectingProduct(product)}
                        title={t.viewDetails}
                        className="p-2 rounded-xl text-gray-500 hover:bg-[#eff4ff] hover:text-[#006194] transition-colors"
                      >
                        <Eye className="w-4 h-4" />
                      </button>
                      <button
                        onClick={() => handleOpenEdit(product)}
                        title={t.editProduct}
                        className="p-2 rounded-xl text-gray-500 hover:bg-gray-100 hover:text-[#006948] transition-colors"
                      >
                        <Edit3 className="w-4 h-4" />
                      </button>
                      <button
                        onClick={() => setDeletingProductId(product.id)}
                        title={t.deleteProduct}
                        className="p-2 rounded-xl text-gray-400 hover:bg-red-50 hover:text-red-600 transition-colors"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  </div>

                </div>
              );
            })}
          </div>
        )}
      </div>

      {/* Add / Edit Product Modal */}
      {isAddModalOpen && (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in duration-200">
          <div className="bg-white rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl border border-gray-100 flex flex-col">
            
            {/* Modal Header */}
            <div className="p-5 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-10">
              <div className="flex items-center gap-2.5">
                <div className="w-9 h-9 rounded-xl bg-[#ecfdf5] text-[#006948] flex items-center justify-center">
                  <Package className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="font-extrabold text-base text-[#0b1c30]">
                    {editingProduct ? t.editProduct : t.addProduct}
                  </h3>
                  <p className="text-xs text-gray-400">
                    {language === 'ar' ? 'أدخل تفاصيل الصنف بدقة لظهوره في تطبيق زادون للعملاء' : 'Enter accurate details for customer app'}
                  </p>
                </div>
              </div>
              <button
                onClick={() => setIsAddModalOpen(false)}
                className="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 transition-colors"
              >
                ✕
              </button>
            </div>

            {/* Modal Form */}
            <form onSubmit={handleFormSubmit} className="p-6 space-y-4">
              
              {/* Names */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    {t.productNameAr} <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    value={formData.nameAr}
                    onChange={e => setFormData({ ...formData, nameAr: e.target.value })}
                    placeholder="مثال: بنادول إكسترا 24 قرص"
                    className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    {t.productNameEn}
                  </label>
                  <input
                    type="text"
                    dir="ltr"
                    value={formData.nameEn}
                    onChange={e => setFormData({ ...formData, nameEn: e.target.value })}
                    placeholder="e.g. Panadol Extra 24 Tablets"
                    className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none text-left"
                  />
                </div>
              </div>

              {/* Descriptions (Arabic & English) */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    {t.productDescAr}
                  </label>
                  <textarea
                    rows={3}
                    value={formData.descriptionAr}
                    onChange={e => setFormData({ ...formData, descriptionAr: e.target.value })}
                    placeholder={t.productDescArPlaceholder}
                    className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none resize-none"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    {t.productDescEn}
                  </label>
                  <textarea
                    rows={3}
                    dir="ltr"
                    value={formData.descriptionEn}
                    onChange={e => setFormData({ ...formData, descriptionEn: e.target.value })}
                    placeholder={t.productDescEnPlaceholder}
                    className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none resize-none text-left"
                  />
                </div>
              </div>

              {/* Category, SKU, Barcode */}
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    {t.category} <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    value={formData.category}
                    onChange={e => setFormData({ ...formData, category: e.target.value })}
                    placeholder="مثال: مسكنات وأدوية"
                    className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    {t.sku}
                  </label>
                  <input
                    type="text"
                    dir="ltr"
                    value={formData.sku}
                    onChange={e => setFormData({ ...formData, sku: e.target.value })}
                    className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none text-left font-mono"
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    {t.barcode}
                  </label>
                  <input
                    type="text"
                    dir="ltr"
                    value={formData.barcode}
                    onChange={e => setFormData({ ...formData, barcode: e.target.value })}
                    placeholder="628XXXXXXXXX"
                    className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none text-left font-mono"
                  />
                </div>
              </div>

              {/* Prices and Stock Math */}
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 bg-[#f8f9ff] rounded-2xl border border-[#e2e8f0]">
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    {t.sellingPrice} ({t.sar}) <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="number"
                    step="0.25"
                    min="0"
                    required
                    value={formData.price}
                    onChange={e => setFormData({ ...formData, price: parseFloat(e.target.value) || 0 })}
                    className="w-full text-xs sm:text-sm p-2.5 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none bg-white font-bold"
                  />
                </div>

                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    {t.costPrice} ({t.sar})
                  </label>
                  <input
                    type="number"
                    step="0.25"
                    min="0"
                    value={formData.costPrice}
                    onChange={e => setFormData({ ...formData, costPrice: parseFloat(e.target.value) || 0 })}
                    className="w-full text-xs sm:text-sm p-2.5 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none bg-white"
                  />
                </div>

                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    {t.stockQuantity} <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="number"
                    min="0"
                    required
                    value={formData.stock}
                    onChange={e => setFormData({ ...formData, stock: parseInt(e.target.value) || 0 })}
                    className="w-full text-xs sm:text-sm p-2.5 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none bg-white font-black text-[#006948]"
                  />
                </div>

                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    {t.minAlertThreshold}
                  </label>
                  <input
                    type="number"
                    min="1"
                    value={formData.minStockAlert}
                    onChange={e => setFormData({ ...formData, minStockAlert: parseInt(e.target.value) || 5 })}
                    className="w-full text-xs sm:text-sm p-2.5 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none bg-white text-amber-700 font-bold"
                  />
                </div>
              </div>

              {/* Unit & Storage condition */}
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    {t.unit} (مثال: 24 قرص، 1 كجم)
                  </label>
                  <input
                    type="text"
                    value={formData.unitAr}
                    onChange={e => setFormData({ ...formData, unitAr: e.target.value })}
                    className="w-full text-xs sm:text-sm p-3 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none"
                  />
                </div>

                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    {t.countryOfOrigin}
                  </label>
                  <div className="relative">
                    <Globe className="w-4 h-4 text-gray-400 absolute right-3 top-3.5" />
                    <input
                      type="text"
                      value={formData.countryOfOrigin}
                      onChange={e => setFormData({ ...formData, countryOfOrigin: e.target.value })}
                      placeholder={language === 'ar' ? 'مثال: المملكة العربية السعودية، ألمانيا، فرنسا' : 'e.g., Saudi Arabia, Germany'}
                      className="w-full text-xs sm:text-sm p-3 pr-9 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    {t.storageMethod}
                  </label>
                  <div className="relative">
                    <ShieldCheck className="w-4 h-4 text-gray-400 absolute right-3 top-3.5" />
                    <input
                      type="text"
                      value={formData.storageMethod}
                      onChange={e => setFormData({ ...formData, storageMethod: e.target.value })}
                      placeholder={language === 'ar' ? 'مثال: يحفظ في مكان بارد وجاف بدرجة حرارة أقل من 25 مئوية' : 'e.g., Store in a cool, dry place'}
                      className="w-full text-xs sm:text-sm p-3 pr-9 rounded-xl border border-gray-300 focus:border-[#006948] focus:outline-none"
                    />
                  </div>
                </div>
              </div>

              {/* Traditional Image Upload Section */}
              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">
                  {t.productImage}
                </label>
                
                {/* Hidden file input */}
                <input
                  ref={fileInputRef}
                  type="file"
                  accept="image/*"
                  onChange={handleFileChange}
                  className="hidden"
                />

                {formData.image ? (
                  <div className="flex items-center gap-4 p-3.5 rounded-2xl border border-gray-200 bg-[#f8f9ff]">
                    <div className="w-20 h-20 rounded-xl bg-white border border-gray-200 overflow-hidden flex items-center justify-center p-1 shrink-0 shadow-2xs">
                      <img
                        src={formData.image}
                        alt="Product Preview"
                        className="w-full h-full object-contain"
                      />
                    </div>
                    <div className="flex-1 min-w-0 space-y-1.5">
                      <p className="text-xs font-bold text-[#0b1c30] flex items-center gap-1.5">
                        <CheckCircle2 className="w-4 h-4 text-emerald-600" />
                        <span>{language === 'ar' ? 'تم اختيار صورة المنتج بنجاح' : 'Product image selected'}</span>
                      </p>
                      <p className="text-[11px] text-gray-500">
                        {language === 'ar' ? 'يمكنك تغيير الصورة أو حذفها في أي وقت' : 'You can change or remove the image at any time'}
                      </p>
                      <div className="flex items-center gap-2 pt-1">
                        <button
                          type="button"
                          onClick={() => fileInputRef.current?.click()}
                          className="px-3 py-1.5 rounded-lg bg-white border border-gray-300 hover:border-[#006948] text-gray-700 hover:text-[#006948] text-xs font-bold transition-colors shadow-2xs flex items-center gap-1"
                        >
                          <UploadCloud className="w-3.5 h-3.5" />
                          <span>{t.changeImage}</span>
                        </button>
                        <button
                          type="button"
                          onClick={() => setFormData(prev => ({ ...prev, image: '' }))}
                          className="px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold transition-colors flex items-center gap-1"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                          <span>{t.removeImage}</span>
                        </button>
                      </div>
                    </div>
                  </div>
                ) : (
                  <div
                    onDragOver={(e) => {
                      e.preventDefault();
                      setIsDragging(true);
                    }}
                    onDragLeave={() => setIsDragging(false)}
                    onDrop={handleDrop}
                    onClick={() => fileInputRef.current?.click()}
                    className={`border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all ${
                      isDragging
                        ? 'border-[#006948] bg-emerald-50/50 scale-[0.99]'
                        : 'border-gray-300 hover:border-[#006948] hover:bg-[#f8f9ff]'
                    }`}
                  >
                    <div className="w-12 h-12 mx-auto mb-3 rounded-full bg-emerald-50 flex items-center justify-center text-[#006948]">
                      <UploadCloud className="w-6 h-6" />
                    </div>
                    <p className="text-xs sm:text-sm font-bold text-[#0b1c30] mb-1">
                      {t.clickOrDragToUpload}
                    </p>
                    <p className="text-[11px] text-gray-400">
                      {t.supportedImageFormats}
                    </p>
                    <button
                      type="button"
                      className="mt-3 px-4 py-1.5 rounded-xl bg-[#006948] hover:bg-[#00855d] text-white text-xs font-bold shadow-2xs transition-colors"
                    >
                      {t.browseFiles}
                    </button>
                  </div>
                )}
              </div>

              {/* Modal Footer Actions */}
              <div className="pt-4 border-t border-gray-100 flex items-center justify-end gap-2">
                <button
                  type="button"
                  onClick={() => setIsAddModalOpen(false)}
                  className="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-xs font-bold hover:bg-gray-50"
                >
                  {t.cancel}
                </button>
                <button
                  type="submit"
                  className="px-6 py-2.5 rounded-xl bg-[#006948] hover:bg-[#00855d] text-white text-xs font-bold shadow-xs active:scale-95 transition-all"
                >
                  {editingProduct ? t.saveChanges : t.addProduct}
                </button>
              </div>

            </form>
          </div>
        </div>
      )}

      {/* Delete Confirmation Modal */}
      {deletingProductId && (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in duration-200">
          <div className="bg-white rounded-3xl max-w-sm w-full p-6 text-center space-y-4 shadow-2xl">
            <div className="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
              <Trash2 className="w-6 h-6" />
            </div>
            <h4 className="font-extrabold text-base text-[#0b1c30]">هل ترغب في حذف هذا المنتج نهائياً؟</h4>
            <p className="text-xs text-gray-500">
              سيتم إزالة المنتج من كتالوج المتجر ولن يظهر للعملاء في تطبيق زادون.
            </p>
            <div className="flex items-center gap-2 pt-2">
              <button
                onClick={() => {
                  deleteProduct(deletingProductId);
                  setDeletingProductId(null);
                }}
                className="flex-1 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-xs"
              >
                نعم، احذف المنتج
              </button>
              <button
                onClick={() => setDeletingProductId(null)}
                className="flex-1 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs"
              >
                {t.cancel}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Product Detail Inspector Modal */}
      <ProductDetailModal
        product={inspectingProduct}
        isOpen={inspectingProduct !== null}
        onClose={() => {
          setInspectingProduct(null);
          if (onSelectProduct) onSelectProduct(null);
        }}
        onEdit={(prod) => {
          setInspectingProduct(null);
          handleOpenEdit(prod);
        }}
      />

    </div>
  );
};
