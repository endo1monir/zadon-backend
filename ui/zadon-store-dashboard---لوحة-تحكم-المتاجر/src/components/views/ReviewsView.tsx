import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { translations } from '../../utils/translations';
import { 
  Star, 
  MessageSquare, 
  CheckCircle2, 
  Send, 
  CornerDownRight, 
  CornerDownLeft, 
  Filter, 
  Search,
  ShoppingBag,
  Sparkles
} from 'lucide-react';

export const ReviewsView: React.FC = () => {
  const { currentStore, reviews, addReviewReply, language } = useStore();
  const t = translations[language];

  const [filterRating, setFilterRating] = useState<number | 'all'>('all');
  const [filterReplied, setFilterReplied] = useState<'all' | 'replied' | 'unreplied'>('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [activeReplyId, setActiveReplyId] = useState<string | null>(null);
  const [replyText, setReplyText] = useState('');

  // Rating analytics calculation
  const totalReviews = reviews.length;
  const avgRating = totalReviews > 0 
    ? (reviews.reduce((acc, r) => acc + r.rating, 0) / totalReviews).toFixed(1) 
    : '0.0';

  const ratingCounts = [5, 4, 3, 2, 1].map(stars => ({
    stars,
    count: reviews.filter(r => r.rating === stars).length,
    percentage: totalReviews > 0 ? Math.round((reviews.filter(r => r.rating === stars).length / totalReviews) * 100) : 0
  }));

  const filteredReviews = reviews.filter(rev => {
    if (filterRating !== 'all' && rev.rating !== filterRating) return false;
    if (filterReplied === 'replied' && !rev.storeReply) return false;
    if (filterReplied === 'unreplied' && rev.storeReply) return false;
    if (searchQuery.trim()) {
      const q = searchQuery.toLowerCase();
      const matchName = rev.customerName.toLowerCase().includes(q);
      const matchComment = rev.comment.toLowerCase().includes(q);
      const matchOrder = rev.orderNumber ? rev.orderNumber.toLowerCase().includes(q) : false;
      if (!matchName && !matchComment && !matchOrder) return false;
    }
    return true;
  });

  const handleSendReply = (reviewId: string) => {
    if (!replyText.trim()) return;
    addReviewReply(reviewId, replyText.trim());
    setActiveReplyId(null);
    setReplyText('');
  };

  const isRtl = language === 'ar';

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-[#0b1c30] flex items-center gap-2.5">
            <Star className="w-7 h-7 text-amber-500 fill-amber-400" />
            <span>{t.customerReviews}</span>
          </h1>
          <p className="text-sm text-gray-500 mt-1">
            {t.reviewsSubtitle}
          </p>
        </div>
      </div>

      {/* Ratings Summary Card */}
      <div className="bg-white rounded-2xl p-6 border border-[#e2e8f0] shadow-xs">
        <div className="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
          {/* Main Score */}
          <div className="md:col-span-4 text-center md:border-e border-[#e2e8f0] md:pe-6">
            <span className="text-5xl font-black text-[#0b1c30] tracking-tight">{avgRating}</span>
            <div className="flex items-center justify-center gap-1 my-2">
              {[1, 2, 3, 4, 5].map(star => {
                const numAvg = parseFloat(avgRating);
                const isFilled = star <= Math.round(numAvg);
                return (
                  <Star 
                    key={star} 
                    className={`w-5 h-5 ${isFilled ? 'text-amber-400 fill-amber-400' : 'text-gray-200'}`} 
                  />
                );
              })}
            </div>
            <p className="text-xs text-gray-500 font-medium">
              {t.overallRating} ({totalReviews} {t.totalReviews})
            </p>
            {currentStore && (
              <p className="text-xs text-[#006948] font-bold mt-1">
                {language === 'ar' ? currentStore.nameAr : currentStore.nameEn}
              </p>
            )}
          </div>

          {/* Rating Breakdown Bars */}
          <div className="md:col-span-8 space-y-2">
            {ratingCounts.map(item => (
              <button
                key={item.stars}
                onClick={() => setFilterRating(filterRating === item.stars ? 'all' : item.stars)}
                className={`w-full flex items-center gap-3 text-xs p-1.5 rounded-lg transition-colors hover:bg-gray-50 ${
                  filterRating === item.stars ? 'bg-amber-50/80 font-bold' : ''
                }`}
              >
                <div className="flex items-center gap-1 w-12 shrink-0">
                  <span className="font-semibold text-gray-700">{item.stars}</span>
                  <Star className="w-3.5 h-3.5 text-amber-400 fill-amber-400" />
                </div>
                <div className="flex-1 h-2.5 bg-gray-100 rounded-full overflow-hidden">
                  <div 
                    className="h-full bg-amber-400 rounded-full transition-all duration-300"
                    style={{ width: `${item.percentage}%` }}
                  />
                </div>
                <span className="w-10 text-end text-gray-500 font-medium shrink-0">
                  {item.count}
                </span>
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Filter and Search Bar */}
      <div className="bg-white rounded-2xl p-4 border border-[#e2e8f0] shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        {/* Rating Pills */}
        <div className="flex flex-wrap items-center gap-2">
          <button
            onClick={() => setFilterRating('all')}
            className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-colors ${
              filterRating === 'all'
                ? 'bg-[#006948] text-white'
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            }`}
          >
            {language === 'ar' ? 'الكل' : 'All'} ({totalReviews})
          </button>
          {[5, 4, 3, 2, 1].map(stars => {
            const count = reviews.filter(r => r.rating === stars).length;
            if (count === 0) return null;
            return (
              <button
                key={stars}
                onClick={() => setFilterRating(filterRating === stars ? 'all' : stars)}
                className={`px-3 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1 transition-colors ${
                  filterRating === stars
                    ? 'bg-amber-500 text-white'
                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                }`}
              >
                <span>{stars}</span>
                <Star className="w-3 h-3 fill-current" />
                <span className="text-[10px] opacity-80">({count})</span>
              </button>
            );
          })}

          <span className="text-gray-300 mx-1">|</span>

          {/* Reply Status filter */}
          <button
            onClick={() => setFilterReplied(filterReplied === 'unreplied' ? 'all' : 'unreplied')}
            className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-colors ${
              filterReplied === 'unreplied'
                ? 'bg-rose-600 text-white'
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            }`}
          >
            {language === 'ar' ? 'بانتظار الرد' : 'Awaiting Reply'}
          </button>
          <button
            onClick={() => setFilterReplied(filterReplied === 'replied' ? 'all' : 'replied')}
            className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-colors ${
              filterReplied === 'replied'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            }`}
          >
            {language === 'ar' ? 'تم الرد' : 'Replied'}
          </button>
        </div>

        {/* Search input */}
        <div className="relative min-w-[220px]">
          <Search className="w-4 h-4 absolute top-1/2 -translate-y-1/2 text-gray-400 start-3" />
          <input
            type="text"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            placeholder={language === 'ar' ? 'بحث بالاسم، التعليق، الطلب...' : 'Search by name, review, order...'}
            className="w-full ps-9 pe-3 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[#006948] focus:bg-white transition-colors"
          />
        </div>
      </div>

      {/* Reviews List */}
      <div className="space-y-4">
        {filteredReviews.length === 0 ? (
          <div className="bg-white rounded-2xl p-12 text-center border border-[#e2e8f0] shadow-xs">
            <Star className="w-12 h-12 text-gray-300 mx-auto mb-3 stroke-[1.5]" />
            <h3 className="text-base font-bold text-gray-700">{t.noReviewsFound}</h3>
            <p className="text-xs text-gray-400 mt-1">
              {language === 'ar' ? 'جرب تغيير معايير التصفية أو البحث لعرض التقييمات' : 'Try adjusting your filters or search keywords'}
            </p>
          </div>
        ) : (
          filteredReviews.map(rev => {
            const isReplying = activeReplyId === rev.id;

            return (
              <div 
                key={rev.id} 
                className="bg-white rounded-2xl p-5 border border-[#e2e8f0] shadow-xs space-y-4 hover:border-gray-300 transition-all"
              >
                {/* Header: Customer Info & Rating */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                  <div className="flex items-center gap-3">
                    {rev.customerAvatar ? (
                      <img 
                        src={rev.customerAvatar} 
                        alt={rev.customerName}
                        className="w-11 h-11 rounded-full object-cover border border-gray-200 shrink-0" 
                      />
                    ) : (
                      <div className="w-11 h-11 rounded-full bg-emerald-50 text-[#006948] font-bold text-sm flex items-center justify-center shrink-0 border border-emerald-100">
                        {rev.customerName.charAt(0)}
                      </div>
                    )}
                    <div>
                      <div className="flex items-center gap-2">
                        <h4 className="font-bold text-sm text-[#0b1c30]">{rev.customerName}</h4>
                        <span className="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                          <CheckCircle2 className="w-3 h-3 text-emerald-600" />
                          <span>{t.verifiedBuyer}</span>
                        </span>
                      </div>
                      <p className="text-[11px] text-gray-400 mt-0.5">
                        {new Date(rev.date).toLocaleDateString(language === 'ar' ? 'ar-SA' : 'en-US', {
                          year: 'numeric',
                          month: 'short',
                          day: 'numeric',
                          hour: '2-digit',
                          minute: '2-digit'
                        })}
                      </p>
                    </div>
                  </div>

                  {/* Rating Stars & Order Reference */}
                  <div className="flex items-center gap-3 sm:self-auto self-start">
                    {rev.orderNumber && (
                      <span className="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-600 bg-gray-100 px-2.5 py-1 rounded-lg">
                        <ShoppingBag className="w-3.5 h-3.5 text-gray-500" />
                        <span>{rev.orderNumber}</span>
                      </span>
                    )}
                    <div className="flex items-center gap-1 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200/50">
                      {[1, 2, 3, 4, 5].map(s => (
                        <Star 
                          key={s} 
                          className={`w-4 h-4 ${s <= rev.rating ? 'text-amber-400 fill-amber-400' : 'text-gray-200'}`} 
                        />
                      ))}
                      <span className="font-bold text-xs text-amber-900 ms-1">{rev.rating}.0</span>
                    </div>
                  </div>
                </div>

                {/* Comment Text */}
                <p className="text-sm text-gray-800 leading-relaxed ps-1">
                  {rev.comment}
                </p>

                {/* Tags / Highlights */}
                {rev.tags && rev.tags.length > 0 && (
                  <div className="flex flex-wrap gap-1.5 pt-1 ps-1">
                    {rev.tags.map((tag, idx) => (
                      <span 
                        key={idx}
                        className="text-[11px] font-medium text-[#006948] bg-emerald-50/70 border border-emerald-200/50 px-2.5 py-0.5 rounded-md"
                      >
                        {tag}
                      </span>
                    ))}
                  </div>
                )}

                {/* Existing Store Reply */}
                {rev.storeReply && (
                  <div className="bg-[#f8f9ff] border-s-4 border-[#006948] rounded-xl p-3.5 ms-2 mt-2 space-y-1">
                    <div className="flex items-center justify-between text-xs">
                      <div className="flex items-center gap-1.5 font-bold text-[#006948]">
                        {isRtl ? <CornerDownLeft className="w-3.5 h-3.5" /> : <CornerDownRight className="w-3.5 h-3.5" />}
                        <span>{t.storeReply}</span>
                      </div>
                      {rev.storeReplyDate && (
                        <span className="text-[10px] text-gray-400">
                          {new Date(rev.storeReplyDate).toLocaleDateString(language === 'ar' ? 'ar-SA' : 'en-US', {
                            month: 'short',
                            day: 'numeric'
                          })}
                        </span>
                      )}
                    </div>
                    <p className="text-xs text-gray-700 leading-relaxed">
                      {rev.storeReply}
                    </p>
                  </div>
                )}

                {/* Reply Form / Action Button */}
                {!rev.storeReply && !isReplying && (
                  <div className="pt-2 flex justify-end">
                    <button
                      onClick={() => {
                        setActiveReplyId(rev.id);
                        setReplyText('');
                      }}
                      className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-[#006948] bg-emerald-50 hover:bg-emerald-100 transition-colors"
                    >
                      <MessageSquare className="w-3.5 h-3.5" />
                      <span>{t.replyToCustomer}</span>
                    </button>
                  </div>
                )}

                {/* Open Reply Editor */}
                {isReplying && (
                  <div className="pt-2 space-y-2.5 bg-gray-50 p-3 rounded-xl border border-gray-200">
                    <div className="flex items-center justify-between text-xs font-bold text-gray-700">
                      <span>{t.replyToCustomer}</span>
                      <button
                        onClick={() => {
                          setActiveReplyId(null);
                          setReplyText('');
                        }}
                        className="text-gray-400 hover:text-gray-600 text-xs"
                      >
                        {language === 'ar' ? 'إلغاء' : 'Cancel'}
                      </button>
                    </div>
                    <textarea
                      rows={3}
                      value={replyText}
                      onChange={(e) => setReplyText(e.target.value)}
                      placeholder={t.replyPlaceholder}
                      className="w-full text-xs p-2.5 bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-[#006948] transition-colors resize-none"
                    />
                    <div className="flex justify-end gap-2">
                      <button
                        onClick={() => {
                          setActiveReplyId(null);
                          setReplyText('');
                        }}
                        className="px-3 py-1 rounded-lg text-xs font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-100"
                      >
                        {language === 'ar' ? 'إلغاء' : 'Cancel'}
                      </button>
                      <button
                        onClick={() => handleSendReply(rev.id)}
                        disabled={!replyText.trim()}
                        className="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-bold text-white bg-[#006948] hover:bg-[#005238] disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                      >
                        <Send className="w-3.5 h-3.5" />
                        <span>{t.sendReply}</span>
                      </button>
                    </div>
                  </div>
                )}
              </div>
            );
          })
        )}
      </div>
    </div>
  );
};
