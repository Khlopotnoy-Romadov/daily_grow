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
            >
          </div>
          
          <div class="input-hint">
            <span class="hint-icon">ℹ️</span>
            <span class="hint-text">
              Пример: https://yandex.ru/maps/org/company_name/123456789/
            </span>
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
          @click="saveLink"
          class="save-btn"
          :disabled="!yandexLink"
        >
          <span class="btn-icon">💾</span>
          <span>Сохранить настройки</span>
        </button>
        
        <button 
          @click="cancel"
          class="cancel-btn"
        >
          Отмена
        </button>
      </div>
    </div>
    
    <!-- Анимация успешного сохранения -->
    <transition name="fade">
      <div v-if="showSuccess" class="success-message">
        <span class="success-icon">✅</span>
        <span>Ссылка успешно сохранена! Перенаправляем...</span>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  name: 'YandexSettings',
  data() {
    return {
      yandexLink: '',
      showSuccess: false
    }
  },
  methods: {
    saveLink() {
      if (!this.yandexLink) return;
      
      // Показываем сообщение об успехе
      this.showSuccess = true;
      
      // Логируем в консоль
      console.log('🔗 Ссылка сохранена:', this.yandexLink);
      
      // Через секунду переходим на страницу отзывов
      setTimeout(() => {
        this.showSuccess = false;
        this.$router.push('/reviews');
      }, 1000);
    },
    
    cancel() {
      this.yandexLink = '';
      console.log('❌ Отмена сохранения');
    }
  }
}
</script>

<style src="../../css/yandex-settings.css" scoped></style>