<template>
  <div class="register-container">
    <div class="register-form">
      <h2 class="form-title">ユーザー登録</h2>

      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label for="name">ユーザー名</label>
          <input
            type="text"
            id="name"
            v-model="form.name"
            :class="{ 'is-invalid': errors.name }"
            placeholder="ユーザー名を入力"
          >
          <div class="invalid-feedback" v-if="errors.name">{{ errors.name }}</div>
        </div>

        <div class="form-group">
          <label for="email">メールアドレス</label>
          <input
            type="email"
            id="email"
            v-model="form.email"
            :class="{ 'is-invalid': errors.email }"
            placeholder="メールアドレスを入力"
          >
          <div class="invalid-feedback" v-if="errors.email">{{ errors.email }}</div>
        </div>

        <div class="form-group">
          <label for="password">パスワード</label>
          <input
            type="password"
            id="password"
            v-model="form.password"
            :class="{ 'is-invalid': errors.password }"
            placeholder="パスワードを入力"
          >
          <div class="invalid-feedback" v-if="errors.password">{{ errors.password }}</div>
        </div>

        <div class="form-group">
          <label for="password_confirmation">パスワード（確認）</label>
          <input
            type="password"
            id="password_confirmation"
            v-model="form.password_confirmation"
            :class="{ 'is-invalid': errors.password_confirmation }"
            placeholder="パスワードを再入力"
          >
          <div class="invalid-feedback" v-if="errors.password_confirmation">{{ errors.password_confirmation }}</div>
        </div>

        <button type="submit" class="submit-button" :disabled="isSubmitting">
          {{ isSubmitting ? '登録中...' : '登録' }}
        </button>
      </form>

      <div class="login-link">
        すでにアカウントをお持ちですか？
        <a href="/login">ログイン</a>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue';
import axios from 'axios';

export default {
  name: 'Register',
  setup() {
    const isSubmitting = ref(false);
    const errors = reactive({});

    const form = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: ''
    });

    const validateForm = () => {
      let isValid = true;
      errors.value = {};

      if (!form.name) {
        errors.name = 'ユーザー名は必須です';
        isValid = false;
      }

      if (!form.email) {
        errors.email = 'メールアドレスは必須です';
        isValid = false;
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        errors.email = '有効なメールアドレスを入力してください';
        isValid = false;
      }

      if (!form.password) {
        errors.password = 'パスワードは必須です';
        isValid = false;
      } else if (form.password.length < 8) {
        errors.password = 'パスワードは8文字以上で入力してください';
        isValid = false;
      }

      if (form.password !== form.password_confirmation) {
        errors.password_confirmation = 'パスワードが一致しません';
        isValid = false;
      }

      return isValid;
    };

    const handleSubmit = async () => {
      if (!validateForm()) return;

      isSubmitting.value = true;
      try {
        const response = await axios.post('/api/register', form);
        if (response.data.success) {
          window.location.href = '/login';
        }
      } catch (error) {
        if (error.response?.data?.errors) {
          Object.keys(error.response.data.errors).forEach(key => {
            errors[key] = error.response.data.errors[key][0];
          });
        } else {
          errors.general = '登録に失敗しました。もう一度お試しください。';
        }
      } finally {
        isSubmitting.value = false;
      }
    };

    return {
      form,
      errors,
      isSubmitting,
      handleSubmit
    };
  }
};
</script>

<style scoped>
.register-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f5f5f5;
  padding: 2rem;
}

.register-form {
  background: white;
  padding: 2rem;
  border-radius: 1rem;
  width: 100%;
  max-width: 500px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.form-title {
  font-size: 1.8rem;
  font-weight: bold;
  margin-bottom: 2rem;
  color: #333;
  text-align: center;
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  color: #666;
  font-weight: 500;
}

input {
  width: 100%;
  padding: 0.8rem;
  border: 1px solid #ddd;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

input:focus {
  outline: none;
  border-color: #2196F3;
}

input.is-invalid {
  border-color: #ff4444;
}

.invalid-feedback {
  color: #ff4444;
  font-size: 0.9rem;
  margin-top: 0.5rem;
}

.submit-button {
  width: 100%;
  padding: 1rem;
  background-color: #2196F3;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 1.1rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
  margin-top: 1rem;
}

.submit-button:hover {
  background-color: #0b7dda;
}

.submit-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.login-link {
  text-align: center;
  margin-top: 1.5rem;
  color: #666;
}

.login-link a {
  color: #2196F3;
  text-decoration: none;
  font-weight: 500;
}

.login-link a:hover {
  text-decoration: underline;
}
</style>