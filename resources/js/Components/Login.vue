<template>
  <div class="login-container">
    <div class="login-form">
      <h2>ログイン</h2>
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="email">メールアドレス</label>
          <input
            type="email"
            id="email"
            v-model="form.email"
            required
            placeholder="メールアドレスを入力"
          >
        </div>
        <div class="form-group">
          <label for="password">パスワード</label>
          <input
            type="password"
            id="password"
            v-model="form.password"
            required
            placeholder="パスワードを入力"
          >
        </div>
        <button type="submit" class="submit-button" :disabled="isLoading">
          {{ isLoading ? 'ログイン中...' : 'ログイン' }}
        </button>
      </form>
      <div class="register-link">
        <p>アカウントをお持ちでない方は<a href="/register">こちら</a>から登録</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import axios from 'axios';

export default {
  name: 'Login',
  setup() {
    const form = ref({
      email: '',
      password: ''
    });
    const isLoading = ref(false);

    const handleLogin = async () => {
      try {
        isLoading.value = true;
        const response = await axios.post('/api/login', form.value);

        if (response.data.success) {
          // ユーザー情報をローカルストレージに保存
          localStorage.setItem('user', JSON.stringify(response.data.user));
          // ホームページにリダイレクト
          window.location.href = '/';
        }
      } catch (error) {
        if (error.response) {
          alert(error.response.data.message || 'ログインに失敗しました');
        } else {
          alert('通信エラーが発生しました');
        }
      } finally {
        isLoading.value = false;
      }
    };

    return {
      form,
      isLoading,
      handleLogin
    };
  }
};
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f5f5f5;
}

.login-form {
  background: white;
  padding: 2rem;
  border-radius: 0.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

h2 {
  text-align: center;
  margin-bottom: 1.5rem;
  color: #333;
}

.form-group {
  margin-bottom: 1rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  color: #666;
}

input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 0.25rem;
  font-size: 1rem;
}

input:focus {
  outline: none;
  border-color: #2196F3;
}

.submit-button {
  width: 100%;
  padding: 0.75rem;
  background-color: #2196F3;
  color: white;
  border: none;
  border-radius: 0.25rem;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.submit-button:hover {
  background-color: #0b7dda;
}

.submit-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.register-link {
  text-align: center;
  margin-top: 1.5rem;
  color: #666;
}

.register-link a {
  color: #2196F3;
  text-decoration: none;
  font-weight: 500;
}

.register-link a:hover {
  text-decoration: underline;
}
</style>