import React, { useState } from 'react';
import { StoreProvider, useStore } from './context/StoreContext';
import { Header } from './components/Header';
import { Sidebar, TabKey } from './components/Sidebar';
import { OverviewView } from './components/views/OverviewView';
import { ProductsView } from './components/views/ProductsView';
import { OrdersView } from './components/views/OrdersView';
import { ReviewsView } from './components/views/ReviewsView';
import { AnalyticsView } from './components/views/AnalyticsView';
import { StoreProfileView } from './components/views/StoreProfileView';
import { NotificationsView } from './components/views/NotificationsView';
import { RegisterView } from './components/views/RegisterView';
import { AuthModal } from './components/modals/AuthModal';

const DashboardContent: React.FC = () => {
  const { currentStore, language } = useStore();
  const [activeTab, setActiveTab] = useState<TabKey>('overview');
  
  // Modals state
  const [isAuthModalOpen, setIsAuthModalOpen] = useState(false);
  const [authModalMode, setAuthModalMode] = useState<'login' | 'register'>('login');
  const [isAddProductModalOpen, setIsAddProductModalOpen] = useState(false);
  const [selectedOrderId, setSelectedOrderId] = useState<string | null>(null);
  const [selectedProductId, setSelectedProductId] = useState<string | null>(null);

  const handleOpenLogin = () => {
    setAuthModalMode('login');
    setIsAuthModalOpen(true);
  };

  const handleOpenRegister = () => {
    setActiveTab('register');
  };

  const handleSelectOrderFromOverview = (orderId: string) => {
    setSelectedOrderId(orderId);
    setActiveTab('orders');
  };

  const handleOpenAddProductFromOverview = () => {
    setActiveTab('products');
    setIsAddProductModalOpen(true);
  };

  return (
    <div className="min-h-screen bg-[#f8f9ff] text-[#0b1c30] flex flex-col font-sans" dir={language === 'ar' ? 'rtl' : 'ltr'}>
      
      {/* Top Header */}
      <Header 
        onOpenLoginModal={handleOpenLogin}
        onOpenRegisterModal={handleOpenRegister}
        onNavigate={setActiveTab}
        onOpenNotifications={() => setActiveTab('notifications')}
      />

      {/* Main Layout Body */}
      <div className="flex-1 flex max-w-7xl w-full mx-auto px-3 sm:px-6 py-4 sm:py-6 gap-6">
        
        {/* Navigation Sidebar (Desktop + Mobile Floating bottom bar) */}
        <Sidebar 
          activeTab={activeTab} 
          onTabChange={setActiveTab}
          onOpenAddProduct={() => {
            setActiveTab('products');
            setIsAddProductModalOpen(true);
          }}
        />

        {/* Tab View Container */}
        <main className="flex-1 min-w-0 pb-20 md:pb-6">
          {activeTab === 'overview' && (
            <OverviewView 
              onNavigate={setActiveTab}
              onOpenAddProduct={handleOpenAddProductFromOverview}
              onSelectOrder={handleSelectOrderFromOverview}
              onOpenLogin={handleOpenLogin}
            />
          )}

          {activeTab === 'products' && (
            <ProductsView 
              isAddModalOpen={isAddProductModalOpen}
              setIsAddModalOpen={setIsAddProductModalOpen}
              selectedProductId={selectedProductId}
              onSelectProduct={setSelectedProductId}
            />
          )}

          {activeTab === 'orders' && (
            <OrdersView 
              selectedOrderId={selectedOrderId}
              setSelectedOrderId={setSelectedOrderId}
            />
          )}

          {activeTab === 'reviews' && (
            <ReviewsView />
          )}

          {activeTab === 'analytics' && (
            <AnalyticsView />
          )}

          {activeTab === 'notifications' && (
            <NotificationsView 
              onNavigate={setActiveTab}
              onSelectOrder={handleSelectOrderFromOverview}
              onSelectProduct={(prodId) => {
                setSelectedProductId(prodId);
                setActiveTab('products');
              }}
            />
          )}

          {activeTab === 'profile' && (
            <StoreProfileView />
          )}

          {activeTab === 'register' && (
            <RegisterView onNavigate={setActiveTab} />
          )}
        </main>

      </div>

      {/* Auth & Store Selection Modal */}
      <AuthModal
        isOpen={isAuthModalOpen}
        onClose={() => setIsAuthModalOpen(false)}
        initialMode={authModalMode}
        onNavigateToRegister={() => setActiveTab('register')}
      />

    </div>
  );
};

export default function App() {
  return (
    <StoreProvider>
      <DashboardContent />
    </StoreProvider>
  );
}
