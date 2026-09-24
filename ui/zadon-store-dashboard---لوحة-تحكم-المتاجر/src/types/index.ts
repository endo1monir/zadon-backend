export type StoreCategory = 
  | 'pharmacy' 
  | 'supermarket' 
  | 'dates' 
  | 'bakery' 
  | 'produce';

export type StoreStatus = 'open' | 'busy' | 'closed';

export interface Store {
  id: string;
  nameAr: string;
  nameEn: string;
  category: StoreCategory;
  logo: string;
  coverImage: string;
  rating: number;
  ratingCount: number;
  addressAr: string;
  addressEn: string;
  city: string;
  phone: string;
  email: string;
  crNumber: string; // Commercial Registration (السجل التجاري)
  vatNumber: string; // Tax number (الرقم الضريبي 15-digit)
  status: StoreStatus;
  prepTimeMin: number;
  deliveryFee: number;
  minOrder: number;
  createdAt: string;
  managerName: string;
  isVerifiedPartner: boolean;
  isOpen24_7: boolean;
  openingTime: string;
  closingTime: string;
  deliveryRadiusKm: number;
}

export type StorageTemp = 'ambient' | 'chilled' | 'frozen';

export interface Product {
  id: string;
  storeId: string;
  nameAr: string;
  nameEn: string;
  category: string;
  sku: string;
  barcode?: string;
  price: number;
  originalPrice?: number;
  costPrice: number;
  stock: number;
  minStockAlert: number;
  unitAr: string;
  unitEn: string;
  image: string;
  countryOfOrigin?: string;
  storageMethod?: string;
  isPrescriptionRequired?: boolean;
  storageTemp?: StorageTemp;
  expiryDate?: string;
  isActive: boolean;
  salesCount: number;
  descriptionAr?: string;
  descriptionEn?: string;
}

export interface OrderItem {
  id: string;
  productId: string;
  productNameAr: string;
  productNameEn: string;
  price: number;
  quantity: number;
  unit: string;
  image?: string;
  packed: boolean;
}

export type OrderStatus = 
  | 'new'               // طلب جديد (New)
  | 'preparing'         // قيد التجهيز (In Preparation)
  | 'ready_for_pickup'  // جاهز للاستلام (Ready for Courier)
  | 'out_for_delivery'  // في الطريق مع المندوب (Out for Delivery)
  | 'delivered'         // تم التسليم (Delivered)
  | 'cancelled';        // ملغي (Cancelled)

export type PaymentMethod = 'mada' | 'apple_pay' | 'credit_card' | 'cash';

export interface Order {
  id: string;
  orderNumber: string; // e.g. ZD-88492
  storeId: string;
  customerName: string;
  customerPhone: string;
  deliveryAddress: string;
  city: string;
  status: OrderStatus;
  createdAt: string;
  updatedAt: string;
  items: OrderItem[];
  paymentMethod: PaymentMethod;
  paymentStatus: 'paid' | 'pending';
  subtotal: number;
  vat15: number;
  deliveryFee: number;
  total: number;
  courierName?: string;
  courierPhone?: string;
  courierEtaMinutes?: number;
  courierAvatar?: string;
  notes?: string;
}

export interface StockAdjustment {
  id: string;
  productId: string;
  productName: string;
  storeId: string;
  type: 'sale' | 'restock' | 'correction' | 'waste';
  quantity: number;
  previousStock: number;
  newStock: number;
  timestamp: string;
  reason?: string;
}

export type NotificationType = 'order' | 'inventory' | 'system' | 'alert';

export interface AppNotification {
  id: string;
  storeId: string;
  titleAr: string;
  titleEn: string;
  messageAr: string;
  messageEn: string;
  type: NotificationType;
  timestamp: string;
  isRead: boolean;
  orderId?: string;
  productId?: string;
  actionLabelAr?: string;
  actionLabelEn?: string;
}

export interface Review {
  id: string;
  storeId: string;
  customerName: string;
  customerAvatar?: string;
  rating: number; // 1 to 5
  date: string;
  comment: string;
  orderNumber?: string;
  tags?: string[];
  storeReply?: string;
  storeReplyDate?: string;
}

export type TabKey = 'overview' | 'products' | 'orders' | 'reviews' | 'analytics' | 'profile' | 'notifications' | 'register';
