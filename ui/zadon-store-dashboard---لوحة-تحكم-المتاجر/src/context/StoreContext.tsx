import React, { createContext, useContext, useState, useEffect } from 'react';
import { Store, Product, Order, OrderStatus, StockAdjustment, StoreStatus, AppNotification, Review } from '../types';
import { INITIAL_STORES, INITIAL_PRODUCTS, INITIAL_ORDERS, INITIAL_NOTIFICATIONS, INITIAL_REVIEWS } from '../data/initialData';

interface StoreContextType {
  stores: Store[];
  currentStore: Store | null;
  products: Product[];
  orders: Order[];
  reviews: Review[];
  notifications: AppNotification[];
  unreadNotificationsCount: number;
  language: 'ar' | 'en';
  toggleLanguage: () => void;
  registerStore: (storeData: Omit<Store, 'id' | 'createdAt' | 'rating' | 'ratingCount' | 'isVerifiedPartner'>) => Store;
  loginStore: (storeId: string) => boolean;
  loginWithPhoneAndPassword: (phone: string, password: string) => { success: boolean; error?: string; store?: Store };
  logoutStore: () => void;
  updateStoreProfile: (updatedData: Partial<Store>) => void;
  setStoreStatus: (status: StoreStatus) => void;
  addProduct: (productData: Omit<Product, 'id' | 'storeId' | 'salesCount'>) => Product;
  updateProduct: (id: string, productData: Partial<Product>) => void;
  deleteProduct: (id: string) => void;
  adjustStock: (productId: string, quantityDelta: number, reason?: string) => void;
  updateOrderStatus: (orderId: string, newStatus: OrderStatus) => void;
  toggleOrderItemPacked: (orderId: string, itemId: string) => void;
  simulateIncomingOrder: () => void;
  addReviewReply: (reviewId: string, replyText: string) => void;
  markNotificationAsRead: (id: string) => void;
  markAllNotificationsAsRead: () => void;
  deleteNotification: (id: string) => void;
  clearAllNotifications: () => void;
  addNotification: (data: Omit<AppNotification, 'id' | 'timestamp' | 'isRead'>) => void;
  stockAdjustments: StockAdjustment[];
  lowStockProducts: Product[];
  outOfStockProducts: Product[];
  activeOrdersCount: number;
  newOrdersCount: number;
  todayRevenue: number;
}

const StoreContext = createContext<StoreContextType | undefined>(undefined);

const STORAGE_KEYS = {
  STORES: 'zadon_merchant_stores_v1',
  CURRENT_STORE_ID: 'zadon_merchant_current_store_id_v1',
  PRODUCTS: 'zadon_merchant_products_v1',
  ORDERS: 'zadon_merchant_orders_v1',
  STOCK_LOGS: 'zadon_merchant_stock_logs_v1',
  NOTIFICATIONS: 'zadon_merchant_notifications_v1',
  REVIEWS: 'zadon_merchant_reviews_v1',
  LANG: 'zadon_merchant_lang_v1',
};

export const StoreProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  // 1. Language state
  const [language, setLanguage] = useState<'ar' | 'en'>(() => {
    const saved = localStorage.getItem(STORAGE_KEYS.LANG);
    return saved === 'en' ? 'en' : 'ar';
  });

  useEffect(() => {
    localStorage.setItem(STORAGE_KEYS.LANG, language);
    document.documentElement.dir = language === 'ar' ? 'rtl' : 'ltr';
    document.documentElement.lang = language;
  }, [language]);

  const toggleLanguage = () => {
    setLanguage(prev => (prev === 'ar' ? 'en' : 'ar'));
  };

  // 2. Stores state
  const [stores, setStores] = useState<Store[]>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_KEYS.STORES);
      return saved ? JSON.parse(saved) : INITIAL_STORES;
    } catch {
      return INITIAL_STORES;
    }
  });

  const [currentStoreId, setCurrentStoreId] = useState<string | null>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_KEYS.CURRENT_STORE_ID);
      if (saved) return saved;
      return INITIAL_STORES[0]?.id || null;
    } catch {
      return INITIAL_STORES[0]?.id || null;
    }
  });

  // 3. Products state
  const [allProducts, setAllProducts] = useState<Product[]>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_KEYS.PRODUCTS);
      return saved ? JSON.parse(saved) : INITIAL_PRODUCTS;
    } catch {
      return INITIAL_PRODUCTS;
    }
  });

  // 4. Orders state
  const [allOrders, setAllOrders] = useState<Order[]>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_KEYS.ORDERS);
      return saved ? JSON.parse(saved) : INITIAL_ORDERS;
    } catch {
      return INITIAL_ORDERS;
    }
  });

  // 5. Stock adjustments history
  const [stockAdjustments, setStockAdjustments] = useState<StockAdjustment[]>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_KEYS.STOCK_LOGS);
      return saved ? JSON.parse(saved) : [];
    } catch {
      return [];
    }
  });

  // 6. Notifications state
  const [allNotifications, setAllNotifications] = useState<AppNotification[]>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_KEYS.NOTIFICATIONS);
      return saved ? JSON.parse(saved) : INITIAL_NOTIFICATIONS;
    } catch {
      return INITIAL_NOTIFICATIONS;
    }
  });

  // 7. Customer Reviews state
  const [allReviews, setAllReviews] = useState<Review[]>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_KEYS.REVIEWS);
      return saved ? JSON.parse(saved) : INITIAL_REVIEWS;
    } catch {
      return INITIAL_REVIEWS;
    }
  });

  // Synchronize with LocalStorage
  useEffect(() => {
    localStorage.setItem(STORAGE_KEYS.STORES, JSON.stringify(stores));
  }, [stores]);

  useEffect(() => {
    if (currentStoreId) {
      localStorage.setItem(STORAGE_KEYS.CURRENT_STORE_ID, currentStoreId);
    } else {
      localStorage.removeItem(STORAGE_KEYS.CURRENT_STORE_ID);
    }
  }, [currentStoreId]);

  useEffect(() => {
    localStorage.setItem(STORAGE_KEYS.PRODUCTS, JSON.stringify(allProducts));
  }, [allProducts]);

  useEffect(() => {
    localStorage.setItem(STORAGE_KEYS.ORDERS, JSON.stringify(allOrders));
  }, [allOrders]);

  useEffect(() => {
    localStorage.setItem(STORAGE_KEYS.STOCK_LOGS, JSON.stringify(stockAdjustments));
  }, [stockAdjustments]);

  useEffect(() => {
    localStorage.setItem(STORAGE_KEYS.NOTIFICATIONS, JSON.stringify(allNotifications));
  }, [allNotifications]);

  useEffect(() => {
    localStorage.setItem(STORAGE_KEYS.REVIEWS, JSON.stringify(allReviews));
  }, [allReviews]);

  // Derived current store
  const currentStore = stores.find(s => s.id === currentStoreId) || null;

  // Filtered products & orders & reviews for current active store
  const products = currentStore ? allProducts.filter(p => p.storeId === currentStore.id) : [];
  const orders = currentStore ? allOrders.filter(o => o.storeId === currentStore.id) : [];
  const reviews = currentStore ? allReviews.filter(r => r.storeId === currentStore.id) : [];
  const notifications = currentStore ? allNotifications.filter(n => n.storeId === currentStore.id) : [];
  const unreadNotificationsCount = notifications.filter(n => !n.isRead).length;

  const addReviewReply = (reviewId: string, replyText: string) => {
    setAllReviews(prev =>
      prev.map(rev =>
        rev.id === reviewId
          ? {
              ...rev,
              storeReply: replyText,
              storeReplyDate: new Date().toISOString()
            }
          : rev
      )
    );
  };

  const addNotification = (data: Omit<AppNotification, 'id' | 'timestamp' | 'isRead'>) => {
    const newNotif: AppNotification = {
      ...data,
      id: `notif-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`,
      timestamp: new Date().toISOString(),
      isRead: false
    };
    setAllNotifications(prev => [newNotif, ...prev]);
  };

  const markNotificationAsRead = (id: string) => {
    setAllNotifications(prev =>
      prev.map(n => (n.id === id ? { ...n, isRead: true } : n))
    );
  };

  const markAllNotificationsAsRead = () => {
    if (!currentStore) return;
    setAllNotifications(prev =>
      prev.map(n => (n.storeId === currentStore.id ? { ...n, isRead: true } : n))
    );
  };

  const deleteNotification = (id: string) => {
    setAllNotifications(prev => prev.filter(n => n.id !== id));
  };

  const clearAllNotifications = () => {
    if (!currentStore) return;
    setAllNotifications(prev => prev.filter(n => n.storeId !== currentStore.id));
  };

  // Register a new store
  const registerStore = (
    storeData: Omit<Store, 'id' | 'createdAt' | 'rating' | 'ratingCount' | 'isVerifiedPartner'>
  ): Store => {
    const newStore: Store = {
      ...storeData,
      id: `store-${Date.now()}`,
      createdAt: new Date().toISOString(),
      rating: 5.0,
      ratingCount: 1,
      isVerifiedPartner: true,
      status: 'open'
    };

    setStores(prev => [newStore, ...prev]);
    setCurrentStoreId(newStore.id);
    return newStore;
  };

  // Login
  const loginStore = (storeId: string): boolean => {
    const exists = stores.find(s => s.id === storeId);
    if (exists) {
      setCurrentStoreId(storeId);
      return true;
    }
    return false;
  };

  // Login with phone and password
  const loginWithPhoneAndPassword = (
    phone: string, 
    password: string
  ): { success: boolean; error?: string; store?: Store } => {
    const trimmedPhone = phone.trim();
    const trimmedPassword = password.trim();

    if (!trimmedPhone) {
      return { 
        success: false, 
        error: language === 'ar' ? 'يرجى إدخال رقم الجوال المسجل' : 'Please enter your registered phone number' 
      };
    }
    if (!trimmedPassword) {
      return { 
        success: false, 
        error: language === 'ar' ? 'يرجى إدخال كلمة المرور' : 'Please enter password' 
      };
    }
    if (trimmedPassword.length < 4) {
      return {
        success: false,
        error: language === 'ar' ? 'كلمة المرور يجب ألا تقل عن 4 خانات' : 'Password must be at least 4 characters'
      };
    }

    const cleanInput = trimmedPhone.replace(/[^0-9]/g, '');

    if (cleanInput.length < 7) {
      return {
        success: false,
        error: language === 'ar' 
          ? 'يرجى إدخال رقم جوال صحيح (مثال: 0501234567)' 
          : 'Please enter a valid phone number (e.g. 0501234567)'
      };
    }

    // Check if phone matches any registered store
    const matched = stores.find(s => {
      const cleanStorePhone = s.phone.replace(/[^0-9]/g, '');
      return (
        cleanStorePhone === cleanInput ||
        cleanStorePhone.endsWith(cleanInput) ||
        cleanInput.endsWith(cleanStorePhone) ||
        cleanStorePhone.includes(cleanInput)
      );
    });

    if (matched) {
      setCurrentStoreId(matched.id);
      return { success: true, store: matched };
    }

    // If a valid mobile format is entered and stores exist, map to the store or allow test login
    if (stores.length > 0 && cleanInput.length >= 8) {
      setCurrentStoreId(stores[0].id);
      return { success: true, store: stores[0] };
    }

    return {
      success: false,
      error: language === 'ar' 
        ? 'بيانات الدخول غير صحيحة. يرجى التحقق من رقم الجوال أو كلمة المرور.' 
        : 'Invalid credentials. Please verify your phone number or password.'
    };
  };

  // Logout
  const logoutStore = () => {
    setCurrentStoreId(null);
  };

  // Update store profile
  const updateStoreProfile = (updatedData: Partial<Store>) => {
    if (!currentStoreId) return;
    setStores(prev =>
      prev.map(store => (store.id === currentStoreId ? { ...store, ...updatedData } : store))
    );
  };

  // Quick store status change
  const setStoreStatus = (status: StoreStatus) => {
    updateStoreProfile({ status });
  };

  // Products CRUD
  const addProduct = (
    productData: Omit<Product, 'id' | 'storeId' | 'salesCount'>
  ): Product => {
    if (!currentStore) throw new Error('No store active');
    const newProduct: Product = {
      ...productData,
      id: `prod-${Date.now()}`,
      storeId: currentStore.id,
      salesCount: 0
    };

    setAllProducts(prev => [newProduct, ...prev]);

    // Add initial stock log
    const adjustment: StockAdjustment = {
      id: `adj-${Date.now()}`,
      productId: newProduct.id,
      productName: newProduct.nameAr,
      storeId: currentStore.id,
      type: 'restock',
      quantity: newProduct.stock,
      previousStock: 0,
      newStock: newProduct.stock,
      timestamp: new Date().toISOString(),
      reason: 'الرصيد الافتتاحي للمنتج الجديد'
    };
    setStockAdjustments(prev => [adjustment, ...prev]);

    return newProduct;
  };

  const updateProduct = (id: string, productData: Partial<Product>) => {
    setAllProducts(prev =>
      prev.map(prod => (prod.id === id ? { ...prod, ...productData } : prod))
    );
  };

  const deleteProduct = (id: string) => {
    setAllProducts(prev => prev.filter(prod => prod.id !== id));
  };

  // Stock adjustments
  const adjustStock = (productId: string, quantityDelta: number, reason?: string) => {
    const product = allProducts.find(p => p.id === productId);
    if (!product) return;

    const previousStock = product.stock;
    const newStock = Math.max(0, previousStock + quantityDelta);

    setAllProducts(prev =>
      prev.map(p => (p.id === productId ? { ...p, stock: newStock } : p))
    );

    const adjustment: StockAdjustment = {
      id: `adj-${Date.now()}`,
      productId,
      productName: product.nameAr,
      storeId: product.storeId,
      type: quantityDelta >= 0 ? 'restock' : 'correction',
      quantity: quantityDelta,
      previousStock,
      newStock,
      timestamp: new Date().toISOString(),
      reason: reason || (quantityDelta >= 0 ? 'تعديل وتوريد يدوي للمخزن' : 'جرد وتصحيح كميات')
    };

    setStockAdjustments(prev => [adjustment, ...prev]);
  };

  // Order status update
  const updateOrderStatus = (orderId: string, newStatus: OrderStatus) => {
    setAllOrders(prev =>
      prev.map(order => {
        if (order.id === orderId) {
          return {
            ...order,
            status: newStatus,
            updatedAt: new Date().toISOString()
          };
        }
        return order;
      })
    );

    // If an order is marked delivered and was in progress, or new status changes
    // Decrement stock when order is accepted/preparing if not already decremented
  };

  const toggleOrderItemPacked = (orderId: string, itemId: string) => {
    setAllOrders(prev =>
      prev.map(order => {
        if (order.id === orderId) {
          const updatedItems = order.items.map(item =>
            item.id === itemId ? { ...item, packed: !item.packed } : item
          );
          return { ...order, items: updatedItems };
        }
        return order;
      })
    );
  };

  // Simulate a live customer placing an order into this store!
  const simulateIncomingOrder = () => {
    if (!currentStore) return;
    const storeProducts = allProducts.filter(p => p.storeId === currentStore.id && p.stock > 0);
    if (storeProducts.length === 0) return;

    // Pick 1 or 2 random products
    const sampleProduct = storeProducts[Math.floor(Math.random() * storeProducts.length)];
    const qty = Math.min(sampleProduct.stock, Math.floor(Math.random() * 2) + 1);

    const subtotal = sampleProduct.price * qty;
    const vat15 = Number((subtotal * 0.15).toFixed(2));
    const deliveryFee = currentStore.deliveryFee;
    const total = Number((subtotal + vat15 + deliveryFee).toFixed(2));

    const randomSuffix = Math.floor(1000 + Math.random() * 9000);
    const customers = [
      { name: 'محمد الدوسري', phone: '+966 50 443 1928', address: 'حي النخيل، طريق الملك فهد' },
      { name: 'هند القحطاني', phone: '+966 55 221 8833', address: 'حي العليا، شارع العروبة، فيلا 22' },
      { name: 'عبدالعزيز الشمري', phone: '+966 54 883 9911', address: 'حي السليمانية، قرب حديقة الملك سلمان' }
    ];
    const customer = customers[Math.floor(Math.random() * customers.length)];

    const newOrder: Order = {
      id: `ord-${Date.now()}`,
      orderNumber: `ZD-${randomSuffix}`,
      storeId: currentStore.id,
      customerName: customer.name,
      customerPhone: customer.phone,
      deliveryAddress: customer.address,
      city: currentStore.city,
      status: 'new',
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
      paymentMethod: Math.random() > 0.4 ? 'mada' : 'apple_pay',
      paymentStatus: 'paid',
      subtotal,
      vat15,
      deliveryFee,
      total,
      courierEtaMinutes: currentStore.prepTimeMin + 10,
      notes: 'توصيل فوري عبر تطبيق زادون',
      items: [
        {
          id: `item-${Date.now()}-1`,
          productId: sampleProduct.id,
          productNameAr: sampleProduct.nameAr,
          productNameEn: sampleProduct.nameEn,
          price: sampleProduct.price,
          quantity: qty,
          unit: sampleProduct.unitAr,
          image: sampleProduct.image,
          packed: false
        }
      ]
    };

    setAllOrders(prev => [newOrder, ...prev]);

    // Automatically decrement stock for ordered item
    adjustStock(sampleProduct.id, -qty, `طلب جديد رقم #${newOrder.orderNumber}`);

    // Create instant notification for the incoming order
    addNotification({
      storeId: currentStore.id,
      titleAr: `طلب جديد وارد #${newOrder.orderNumber}`,
      titleEn: `New Incoming Order #${newOrder.orderNumber}`,
      messageAr: `طلب جديد من ${newOrder.customerName} بقيمة ${newOrder.total.toFixed(2)} ر.س. سارع بتجهيز الطلب.`,
      messageEn: `New order from ${newOrder.customerName} (${newOrder.total.toFixed(2)} SAR). Please prepare promptly.`,
      type: 'order',
      orderId: newOrder.id,
      actionLabelAr: 'معاينة وتجهيز الطلب',
      actionLabelEn: 'View Order'
    });
  };

  // KPIs
  const lowStockProducts = products.filter(p => p.stock > 0 && p.stock <= p.minStockAlert);
  const outOfStockProducts = products.filter(p => p.stock === 0);
  const activeOrdersCount = orders.filter(o => ['new', 'preparing', 'ready_for_pickup', 'out_for_delivery'].includes(o.status)).length;
  const newOrdersCount = orders.filter(o => o.status === 'new').length;
  
  const todayRevenue = orders
    .filter(o => o.status !== 'cancelled')
    .reduce((sum, o) => sum + o.total, 0);

  return (
    <StoreContext.Provider
      value={{
        stores,
        currentStore,
        products,
        orders,
        reviews,
        notifications,
        unreadNotificationsCount,
        language,
        toggleLanguage,
        registerStore,
        loginStore,
        loginWithPhoneAndPassword,
        logoutStore,
        updateStoreProfile,
        setStoreStatus,
        addProduct,
        updateProduct,
        deleteProduct,
        adjustStock,
        updateOrderStatus,
        toggleOrderItemPacked,
        simulateIncomingOrder,
        addReviewReply,
        markNotificationAsRead,
        markAllNotificationsAsRead,
        deleteNotification,
        clearAllNotifications,
        addNotification,
        stockAdjustments,
        lowStockProducts,
        outOfStockProducts,
        activeOrdersCount,
        newOrdersCount,
        todayRevenue
      }}
    >
      {children}
    </StoreContext.Provider>
  );
};

export const useStore = () => {
  const context = useContext(StoreContext);
  if (!context) {
    throw new Error('useStore must be used within a StoreProvider');
  }
  return context;
};
