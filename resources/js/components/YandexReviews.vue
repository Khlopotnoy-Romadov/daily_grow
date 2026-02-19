<template>
  <div class="reviews-container">
    <!-- Заголовок с иконкой Яндекса -->
    <div class="reviews-header">
      <div class="header-left">
        <div class="yandex-badge">
          <div class="yandex-icon">
            <span class="yandex-letter">Я</span>
          </div>
          <div class="yandex-info">
            <h2 class="yandex-title">Яндекс.Карты</h2>
            <!-- <span class="yandex-status">● Активно</span> -->
          </div>
        </div>
      </div>
      
      <!-- <div class="header-right">
        <div class="last-update">
          <span class="update-icon">🔄</span>
          <span>Обновлено только что</span>
        </div>
      </div> -->
    </div>
    
    <div class="reviews-grid">
      <!-- Левая колонка с отзывами -->
      <div class="reviews-column">
        <!-- Панель управления -->
        <!-- <div class="controls-panel">
          <div class="sort-control">
            <label class="sort-label">Сортировка:</label>
            <select 
              v-model="sortOrder"
              class="sort-select"
            >
              <option value="newest">📅 Сначала новые</option>
              <option value="oldest">📅 Сначала старые</option>
              <option value="highest">⭐ Высокий рейтинг</option>
              <option value="lowest">⭐ Низкий рейтинг</option>
            </select>
          </div>
          
          <div class="filter-control">
            <span class="filter-icon">🔍</span>
            <input 
              type="text" 
              placeholder="Поиск по отзывам..."
              class="filter-input"
            >
          </div>
        </div>
         -->
        <!-- Список отзывов -->
        <transition-group name="list" tag="div" class="reviews-list">
          <div 
            v-for="review in paginatedReviews" 
            :key="review.id"
            class="review-card"
          >
            <div class="review-header">
              <div class="reviewer-info">
                <div class="reviewer-avatar">
                  {{ review.author.charAt(0) }}
                </div>
                <div>
                  <h4 class="reviewer-name">{{ review.author }}</h4>
                  <div class="review-meta">
                    <span class="review-date">{{ formatDate(review.date) }}</span>
                  </div>
                </div>
              </div>
              
              <div class="review-rating">
                <div class="rating-stars">
                  <span 
                    v-for="star in 5" 
                    :key="star"
                    class="star"
                    :class="{ 'star-filled': star <= review.rating }"
                  >★</span>
                </div>
                <span class="rating-value">{{ review.rating }}.0</span>
              </div>
            </div>
            
            <p class="review-text">{{ review.text }}</p>
            
            <!-- <div class="review-footer">
              <button class="action-btn">
                <span class="action-icon">👍</span>
                <span>Полезно</span>
              </button>
              <button class="action-btn">
                <span class="action-icon">💬</span>
                <span>Ответить</span>
              </button>
              <span class="review-time">{{ review.time }}</span>
            </div> -->
          </div>
        </transition-group>
        
        <!-- Постраничная навигация -->
        <div class="pagination">
          <button 
            class="pagination-btn"
            :disabled="currentPage === 1"
            @click="currentPage--"
          >
            ←
          </button>
          
          <div class="pagination-pages">
            <button 
              v-for="page in displayedPages" 
              :key="page"
              class="page-btn"
              :class="{ 'page-btn-active': page === currentPage }"
              @click="currentPage = page"
            >
              {{ page }}
            </button>
          </div>
          
          <button 
            class="pagination-btn"
            :disabled="currentPage === totalPages"
            @click="currentPage++"
          >
            →
          </button>
        </div>
      </div>
      
      <!-- Правая колонка с рейтингом -->
      <div class="stats-column">
        <div class="stats-card">
          <h3 class="stats-title">Общий рейтинг</h3>
          
          <div class="rating-circle">
            <svg viewBox="0 0 120 120" class="circular-chart">
              <circle 
                class="circle-bg" 
                cx="60" cy="60" r="54" 
                fill="none" 
                stroke="#e2e8f0" 
                stroke-width="8"
              />
              <circle 
                class="circle-progress" 
                cx="60" cy="60" r="54" 
                fill="none" 
                :stroke="averageRating >= 4 ? '#48bb78' : averageRating >= 3 ? '#ecc94b' : '#f56565'"
                stroke-width="8"
                :stroke-dasharray="circumference"
                :stroke-dashoffset="circleOffset"
                stroke-linecap="round"
              />
            </svg>
            <div class="rating-number">{{ averageRating }}</div>
          </div>
          
          <div class="rating-stats">
            <div class="total-reviews">
              <span class="total-number">{{ totalReviews }}</span>
              <span class="total-text">{{ pluralizeReviews(totalReviews) }}</span>
            </div>
            
            <div class="rating-breakdown">
              <div v-for="star in 5" :key="star" class="breakdown-item">
                <span class="breakdown-star">{{ 6 - star }} ★</span>
                <div class="progress-bar">
                  <div 
                    class="progress-fill"
                    :style="{ width: getRatingPercentage(6 - star) + '%' }"
                  ></div>
                </div>
                <span class="breakdown-count">{{ getRatingCount(6 - star) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useReviewsStore } from '../reviewsStore';

export default {
  name: 'YandexReviews',
  data() {
    return {
      sortOrder: 'newest',
      currentPage: 1,
      itemsPerPage: 3,
      reviews: [],
      // reviews: [
      //   { id: 1, rating: 5, author: 'Анна С.', time: '2 часа назад', date: '2024-01-15', text: 'Отличное место! Очень довольна сервисом. Персонал вежливый, всё быстро и качественно. Обязательно вернусь снова и порекомендую друзьям.' },
      //   { id: 2, rating: 4, author: 'Михаил П.', time: '5 часов назад', date: '2024-01-14', text: 'Хороший сервис, но цены немного высоковаты. В целом всё понравилось, особенно отношение к клиентам. Есть небольшие замечания по срокам.' },
      //   { id: 3, rating: 5, author: 'Елена В.', time: 'вчера', date: '2024-01-13', text: 'Быстро и качественно! Сделали всё в срок, учли все пожелания. Очень приятно иметь дело с профессионалами. Цены адекватные.' },
      //   { id: 4, rating: 3, author: 'Дмитрий К.', time: '2 дня назад', date: '2024-01-12', text: 'Нормально, но могло быть лучше. Есть над чем работать. Персонал приветливый, но организация хромает. Возможно, это временные трудности.' },
      //   { id: 5, rating: 5, author: 'Ольга М.', time: '3 дня назад', date: '2024-01-11', text: 'Обязательно вернусь ещё! Лучшее соотношение цены и качества в городе. Очень довольна результатом. Спасибо большое команде!' },
      //   { id: 6, rating: 4, author: 'Сергей Л.', time: '4 дня назад', date: '2024-01-10', text: 'Хорошее соотношение цены и качества. Работой доволен, но есть небольшие нюансы. В целом рекомендую.' },
      //   { id: 7, rating: 5, author: 'Татьяна Н.', time: '5 дней назад', date: '2024-01-09', text: 'Прекрасный сервис! Очень внимательные сотрудники. Всё объяснили, показали. Результат превзошёл ожидания.' },
      //   { id: 8, rating: 4, author: 'Алексей Б.', time: '6 дней назад', date: '2024-01-08', text: 'Хорошо, но можно лучше. В целом доволен, но есть мелкие недочеты. Обслуживание на высоте.' },
      // ]
    }
  },

  mounted() {
    this.reviewsStore.setAuthor()
    console.log('В mounted', this.reviewsStore.allReviews)
    this.reviews = this.reviewsStore.allReviews
  },  

  computed: {
    averageRating() {
      if (this.reviews.length === 0) return '0.0';
      const sum = this.reviews.reduce((acc, review) => acc + review.rating, 0);
      return (sum / this.reviews.length).toFixed(1);
    },

    reviewsStore(){
      return useReviewsStore();
    },
    
    totalReviews() {
      return this.reviews.length;
    },
    
    sortedReviews() {
      let sorted = [...this.reviews];
      
      switch(this.sortOrder) {
        case 'newest':
          return sorted.sort((a, b) => new Date(b.date) - new Date(a.date));
        case 'oldest':
          return sorted.sort((a, b) => new Date(a.date) - new Date(b.date));
        case 'highest':
          return sorted.sort((a, b) => b.rating - a.rating);
        case 'lowest':
          return sorted.sort((a, b) => a.rating - b.rating);
        default:
          return sorted;
      }
    },
    
    totalPages() {
      return Math.ceil(this.sortedReviews.length / this.itemsPerPage);
    },
    
    paginatedReviews() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.sortedReviews.slice(start, end);
    },
    
    displayedPages() {
      const pages = [];
      const maxVisible = 5;
      let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
      let end = Math.min(this.totalPages, start + maxVisible - 1);
      
      if (end - start + 1 < maxVisible) {
        start = Math.max(1, end - maxVisible + 1);
      }
      
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      
      return pages;
    },
    
    circumference() {
      return 2 * Math.PI * 54;
    },
    
    circleOffset() {
      const percent = (parseFloat(this.averageRating) / 5) * 100;
      return this.circumference - (percent / 100) * this.circumference;
    }
  },
  methods: {
    formatDate(date) {
      const options = { day: 'numeric', month: 'long' };
      return new Date(date).toLocaleDateString('ru-RU', options);
    },
    
    pluralizeReviews(count) {
      if (count % 10 === 1 && count % 100 !== 11) return 'отзыв';
      if ([2,3,4].includes(count % 10) && ![12,13,14].includes(count % 100)) return 'отзыва';
      return 'отзывов';
    },
    
    getRatingCount(rating) {
      return this.reviews.filter(r => r.rating === rating).length;
    },
    
    getRatingPercentage(rating) {
      const count = this.getRatingCount(rating);
      return (count / this.totalReviews) * 100;
    }
  }
}
</script>

<style src="../../css/yandex-reviews.css" scoped></style>