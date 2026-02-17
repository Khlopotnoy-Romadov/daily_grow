<template>
  <div class="settings-container">
    <div class="settings-header">
      <h1 class="settings-title">
        <span class="title-icon">🔌</span>
        Подключить Яндекс
      </h1>
      <p class="settings-subtitle">Интеграция с Яндекс Картами для отзывов</p>
    </div>

    <div class="settings-card">
      <div class="card-header">
        <div class="header-icon">
          <span class="yandex-icon">Я</span>
        </div>
        <div class="header-text">
          <h2 class="card-title">Данные организации</h2>
          <p class="card-description">Введите ссылку на карточку компании в Яндекс Картах</p>
        </div>
      </div>

      <div class="card-body">
        <div class="input-group">
          <label class="input-label">
            <span class="label-text">Ссылка на Яндекс Карты</span>
            <span class="label-required">*</span>
          </label>

          <div class="input-wrapper">
            <span class="input-icon">🔗</span>
            <input 
              type="url" 
              v-model="yandexLink" 
              placeholder="https://yandex.ru/maps/org/..." 
              class="form-input"
              @input="clearError"
            >
          </div>

          <div class="input-hint">
            <span class="hint-icon">ℹ️</span>
            <span class="hint-text">
              Пример: https://yandex.ru/maps/org/company_name/123456789/
            </span>
          </div>

          <!-- Отображение ошибки валидации -->
          <div v-if="validationError" class="error-message">
            <span class="error-icon">⚠️</span>
            <span>{{ validationError }}</span>
          </div>
        </div>

        <div class="info-box">
          <span class="info-icon">💡</span>
          <div class="info-content">
            <p class="info-title">Как найти ссылку?</p>
            <p class="info-text">
              1. Откройте Яндекс Карты<br>
              2. Найдите вашу компанию<br>
              3. Скопируйте ссылку из адресной строки
            </p>
          </div>
        </div>
      </div>

      <div class="card-footer">
        <button 
          @click="saveAndFetchReviews" 
          class="save-btn" 
          :disabled="!yandexLink || loading"
        >
          <span v-if="loading" class="spinner">⏳</span>
          <span v-else class="btn-icon">💾</span>
          <span>{{ loading ? 'Загрузка...' : 'Сохранить и получить отзывы' }}</span>
        </button>

        <button @click="cancel" class="cancel-btn" :disabled="loading">
          Отмена
        </button>
      </div>
    </div>

    <!-- Индикатор загрузки -->
    <div v-if="loading" class="loading-overlay">
      <div class="spinner-large"></div>
      <p>Получение отзывов...</p>
    </div>

    <!-- Анимация успешного сохранения -->
    <transition name="fade">
      <div v-if="showSuccess" class="success-message">
        <span class="success-icon">✅</span>
        <span>Ссылка успешно сохранена! Отзывы получены.</span>
      </div>
    </transition>

    <!-- Отображение ошибки запроса -->
    <transition name="fade">
      <div v-if="error" class="error-message-global">
        <span class="error-icon">❌</span>
        <span>{{ error }}</span>
        <button @click="clearError" class="close-error">✕</button>
      </div>
    </transition>

    <!-- Предпросмотр полученных отзывов (опционально) -->
    <div v-if="reviews.length > 0" class="preview-card">
      <h3>Получено отзывов: {{ reviews.length }}</h3>
      <!-- <div class="preview-list">
        <div v-for="(review, index) in reviews.slice(0, 5)" :key="index" class="preview-item">
          <p class="preview-text">{{ review.text.substring(0, 100) }}...</p>
          <p class="preview-rating">⭐ {{ review.rating }}</p>
        </div>
      </div> -->
      <button @click="goToReviews" class="view-all-btn">
        Посмотреть все отзывы
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useReviewsStore } from '../reviewsStore';

export default {
  name: 'YandexSettings',
  data() {
    return {
      yandexLink: '',
      showSuccess: false,
      reviews: [],
      loading: false,
      error: null,
      validationError: null
    }
  },

  computed: {
    reviewsStore(){
      return useReviewsStore();
    }
  },

  methods: {
    // Валидация ссылки на Яндекс Карты
    validateYandexLink(link) {
      const pattern = /^https?:\/\/(yandex\.(ru|by|kz|uz|com)|maps\.yandex\.(ru|by|kz|uz|com))\/.*/;
      return pattern.test(link);
    },

    // Очистка ошибок
    clearError() {
      this.validationError = null;
      this.error = null;
    },

    // Сохранение и получение отзывов
    async saveAndFetchReviews() {
      // Валидация ссылки
      if (!this.yandexLink) {
        this.validationError = 'Введите ссылку на Яндекс Карты';
        return;
      }

      if (!this.validateYandexLink(this.yandexLink)) {
        this.validationError = 'Введите корректную ссылку на Яндекс Карты';
        return;
      }

      // Сохраняем ссылку
      console.log('🔗 Сохраняем ссылку:', this.yandexLink);

      // Получаем отзывы
      await this.fetchReviews(this.yandexLink);
    },

    // Получение отзывов
    async fetchReviews(url) {
      this.loading = true;
      this.error = null;
      this.reviews = [];
      
      console.log('🔍 Запрос отзывов для URL:', url);
      
      try {
        const response = await axios.post('/api/yandex/parse-reviews', {
          url: url
        });
        
        console.log('✅ Результат запроса:', response);
        
        if (response.data.success) {
          this.reviews = response.data.data;
          this.reviewsStore.allReviews = this.reviews;
          // Показываем сообщение об успехе
          this.showSuccess = true;
          
          // Сохраняем ссылку в localStorage или store (опционально)
          localStorage.setItem('yandex_company_link', url);
          
          // Скрываем сообщение через 3 секунды
          setTimeout(() => {
            this.showSuccess = false;
          }, 3000);
          
          // Если нужно сразу перейти на страницу отзывов
          // this.goToReviews();
          
        } else {
          this.error = response.data.message || 'Не удалось получить отзывы';
        }
      } catch (err) {
        console.error('❌ Ошибка запроса:', err);
        
        if (err.response) {
          // Ошибка от сервера
          this.error = err.response.data.message || 'Ошибка сервера';
        } else if (err.request) {
          // Ошибка сети
          this.error = 'Сервер недоступен. Проверьте подключение к интернету.';
        } else {
          // Другая ошибка
          this.error = 'Произошла неизвестная ошибка';
        }
      } finally {
        this.loading = false;
      }
    },

    // Переход к странице с отзывами
    goToReviews() {
      this.$router.push({
        path: '/reviews',
        query: { source: 'yandex' } // Опционально передаем параметры
      });
    },

    // Отмена
    cancel() {
      this.yandexLink = '';
      this.reviews = [];
      this.error = null;
      this.validationError = null;
      console.log('❌ Отмена сохранения');
    }
  }
}
</script>

<style src="../../css/yandex-settings.css" scoped></style>

<style scoped>
/* Дополнительные стили для новых элементов */
.error-message {
  margin-top: 8px;
  padding: 8px 12px;
  background-color: #fee2e2;
  border: 1px solid #ef4444;
  border-radius: 6px;
  color: #b91c1c;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
}

.error-message-global {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 12px 20px;
  background-color: #fee2e2;
  border: 1px solid #ef4444;
  border-radius: 8px;
  color: #b91c1c;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  z-index: 1000;
  animation: slideIn 0.3s ease;
}

.close-error {
  margin-left: 10px;
  background: none;
  border: none;
  color: #b91c1c;
  cursor: pointer;
  font-size: 16px;
  padding: 0 4px;
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255,255,255,0.8);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 2000;
}

.spinner-large {
  width: 50px;
  height: 50px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #fc3f1d;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.preview-card {
  margin-top: 30px;
  padding: 20px;
  background-color: #f8f9fa;
  border-radius: 12px;
  border: 1px solid #e9ecef;
}

.preview-list {
  margin: 15px 0;
}

.preview-item {
  padding: 10px;
  background-color: white;
  border-radius: 6px;
  margin-bottom: 10px;
  border: 1px solid #dee2e6;
}

.preview-text {
  margin-bottom: 5px;
  font-size: 14px;
}

.preview-rating {
  color: #ffc107;
  font-weight: bold;
}

.view-all-btn {
  width: 100%;
  padding: 10px;
  background-color: #fc3f1d;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s;
}

.view-all-btn:hover {
  background-color: #e03513;
}

.spinner {
  display: inline-block;
  animation: spin 1s linear infinite;
}
</style>