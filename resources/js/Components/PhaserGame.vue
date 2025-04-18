<template>
  <div class="game-wrapper">
    <div class="game-container">
      <div ref="gameContainer" class="game-canvas"></div>

      <!-- モーダルオーバーレイ -->
      <GreetrModal
        :show="showDailyQuestModal"
        :closeOnOverlay="false"
        @close="showDailyQuestModal = false"
      >
        <template #title>
          <div class="daily-quest-header">
            <img src="/assets/hero.png" alt="Hero" class="hero-image">
            <h2 class="daily-quest-title">デイリークエスト</h2>
          </div>
        </template>
        <div class="daily-quest-content">
          <div v-if="quests.length === 0" class="empty-quests">
            <p>クエストが登録されていません</p>
            <p class="empty-quests-hint">「クエストを追加」ボタンで新しいクエストを追加できます</p>
          </div>
          <div v-else v-for="(quest, index) in quests" :key="index" class="quest-item">
            <input
              type="text"
              v-model="quests[index]"
              class="quest-input"
              placeholder="クエストを入力"
            >
            <button class="delete-button" @click="removeQuest(index)">×</button>
          </div>
        </div>
        <template #footer>
          <button class="btn add-quest" @click="addQuest">クエストを追加</button>
          <button class="btn" @click="closeModal">OK</button>
        </template>
      </GreetrModal>
    </div>
  </div>
</template>

<script>
import { onMounted, onUnmounted, ref, nextTick, watch } from 'vue';
import * as Phaser from 'phaser';
import axios from 'axios';
import GreetrModal from './GreetrModal.vue';

export default {
  name: 'PhaserGame',
  components: {
    GreetrModal
  },
  setup() {
    const gameContainer = ref('gameContainer');
    const game = ref(null);
    const currentLogin = ref(null);
    const apiError = ref(null);
    const showDailyQuestModal = ref(false);
    const modalMessage = ref('');
    const quests = ref(['']);
    const weeklyBonus = ref([]);
    let loginBonusScene = null;

    // ログインボーナス一覧を取得
    const fetchWeeklyBonus = async () => {
      try {
        const response = await axios.get('/api/world-entry/weekly-bonus');
        if (response.data.success) {
          weeklyBonus.value = response.data.weeklyBonus;
          // ゲームシーンが存在する場合は更新
          if (loginBonusScene) {
            loginBonusScene.updateLoginStatus();
          }
        }
      } catch (error) {
        console.error('ログインボーナス一覧の取得に失敗しました:', error);
      }
    };

    // コンポーネントマウント時にログインボーナス一覧を取得
    onMounted(() => {
      fetchWeeklyBonus();
    });

    // クエストの追加
    const addQuest = () => {
      quests.value.push('');
      updateQuestBadge();
    };

    // クエストの削除
    const removeQuest = (index) => {
      quests.value.splice(index, 1);
      updateQuestBadge();
    };

    // バッジの更新
    const updateQuestBadge = () => {
      if (loginBonusScene) {
        const scene = loginBonusScene;
        const count = quests.value.filter(q => q.trim() !== '').length;

        // バッジのテキストと表示状態を更新
        scene.children.list.forEach(child => {
          if (child.type === 'Text' && child.questBadgeText) {
            child.setText(count.toString());
            child.setVisible(count > 0);
          }
          if (child.type === 'Arc' && child.questBadgeBg) {
            child.setVisible(count > 0);
          }
        });
      }
    };

    // クエストの変更を監視
    watch(quests, () => {
      updateQuestBadge();
    }, { deep: true });

    // モーダルを表示する関数
    const showModal = (message) => {
      modalMessage.value = message;
      showDailyQuestModal.value = true;
      // ゲームを一時停止
      if (game && game.value.scene.scenes.length > 0) {
        game.value.scene.scenes.forEach(scene => {
          if (scene.scene.isActive()) {
            scene.scene.pause();
          }
        });
      }
    };

    // モーダルを閉じる関数
    const closeModal = () => {
      showDailyQuestModal.value = false;
      // ゲームを再開
      if (game && game.value.scene.scenes.length > 0) {
        game.value.scene.scenes.forEach(scene => {
          if (scene.scene.isPaused()) {
            scene.scene.resume();
          }
        });
      }
    };

    // ワールド入場処理
    const handleWorldEntry = () => {
      if (!currentLogin.value) {
        loginBonusScene.statusText.setText('ワールドを選択してください');
        loginBonusScene.statusText.setTint(0xff0000);
        return;
      }

      // デイリークエストを取得（空のクエストを除外）
      const dailyQuests = quests.value.filter(q => q.trim() !== '');

      // ワールドエントリーAPIを呼び出し
      axios.post(`/api/world-entry`, {
        daily_quests: dailyQuests
      })
        .then(response => {
          // 成功メッセージ
          loginBonusScene.statusText.setText(`ワールド入場成功: ${response.data.reward}コインを獲得しました！`);
          loginBonusScene.statusText.setTint(0x00ff00);

          // ログインボーナス一覧を再取得
          fetchWeeklyBonus();
        })
        .catch(error => {
          console.error('ワールド入場エラー:', error);
          loginBonusScene.statusText.setText('ワールド入場に失敗しました');
          loginBonusScene.statusText.setTint(0xff0000);
        });
    };

    // ログインボーナスシーン
    class LoginBonusScene extends Phaser.Scene {
      constructor() {
        super({ key: 'LoginBonusScene' });
        this.weekdays = ['月', '火', '水', '木', '金'];
        this.rewards = [100, 200, 300, 400, 500];
        this.loginStatus = {};
        this.currentDate = new Date();
        this.weekdayButtons = [];
        this.loginButton = null;
        this.statusText = null;
        this.backgroundImage = null;
        loginBonusScene = this;

        this.currentDate = new Date(new Date().toLocaleString('ja-JP', { timeZone: 'Asia/Tokyo' }));
      }

      preload() {
        // 背景画像をプリロード
        this.load.image('background', '/assets/bg.png');
        // コイン画像をプリロード
        this.load.image('coin', '/assets/coin.png');
        // コインセル画像をプリロード
        this.load.image('box', '/assets/box.png');
      }

      create() {
        const screenWidth = this.scale.width;
        const screenHeight = this.scale.height;
        const centerX = screenWidth / 2;
        const centerY = screenHeight / 2;

        // 背景画像を表示
        this.backgroundImage = this.add.image(0, 0, 'background');
        this.backgroundImage.setOrigin(0, 0);
        this.backgroundImage.setScale(screenWidth / this.backgroundImage.width);

        // 背景画像の上に半透明の黒いレイヤーを重ねる（透明度を0.7から0.4に変更）
        this.add.rectangle(centerX, centerY, screenWidth, screenHeight, 0x000000, 0.4).setOrigin(0.5);

        // タイトル
        this.add.text(centerX, centerY * 0.3, '今週のログインボーナス', {
          fontFamily: 'Arial',
          fontSize: Math.min(screenWidth * 0.05, 32),
          color: '#ffffff'
        }).setOrigin(0.5);

        // 現在の日付と曜日を表示
        const today = this.currentDate;
        const todayText = `${today.getFullYear()}年${today.getMonth() + 1}月${today.getDate()}日（${this.getJapaneseWeekday(today)}）`;
        this.add.text(centerX, centerY * 0.5, `今日は${todayText}です`, {
          fontFamily: 'Arial',
          fontSize: Math.min(screenWidth * 0.04, 24),
          color: '#ffff00'
        }).setOrigin(0.5);

        // ステータステキスト (API応答表示用)
        this.statusText = this.add.text(centerX, screenHeight * 0.9, '', {
          fontFamily: 'Arial',
          fontSize: Math.min(screenWidth * 0.03, 16),
          color: '#ffffff',
          align: 'center'
        }).setOrigin(0.5);

        // モーダル表示ボタン
        const buttonWidth = screenWidth * 0.2;
        const buttonHeight = screenHeight * 0.06;
        const buttonX = screenWidth * 0.8;
        const buttonY = screenHeight * 0.1;

        const modalButtonBg = this.add.rectangle(buttonX, buttonY, buttonWidth, buttonHeight, 0x2196F3, 1)
          .setStrokeStyle(2, 0x0b7dda)
          .setInteractive();

        const modalButtonText = this.add.text(buttonX, buttonY, 'クエスト', {
          fontFamily: 'Arial',
          fontSize: Math.min(screenWidth * 0.03, 18),
          color: '#ffffff'
        }).setOrigin(0.5);

        // クエスト数のバッジ
        const badgeRadius = Math.min(screenWidth * 0.02, 15);
        const badgeX = buttonX + buttonWidth * 0.4;
        const badgeY = buttonY - buttonHeight * 0.3;

        const badgeBg = this.add.circle(badgeX, badgeY, badgeRadius, 0xff4444, 1);
        badgeBg.questBadgeBg = true;

        const badgeText = this.add.text(badgeX, badgeY, quests.value.filter(q => q.trim() !== '').length.toString(), {
          fontFamily: 'Arial',
          fontSize: Math.min(screenWidth * 0.02, 12),
          color: '#ffffff'
        }).setOrigin(0.5);
        badgeText.questBadgeText = true;

        // 初期表示
        const count = quests.value.filter(q => q.trim() !== '').length;
        badgeBg.setVisible(count > 0);
        badgeText.setVisible(count > 0);

        // ボタンのホバー効果
        modalButtonBg.on('pointerover', () => {
          modalButtonBg.fillColor = 0x0b7dda;
        });

        modalButtonBg.on('pointerout', () => {
          modalButtonBg.fillColor = 0x2196F3;
        });

        // ボタンクリックでモーダルを表示
        modalButtonBg.on('pointerdown', () => {
          showModal('デイリークエストを確認しましょう！');
        });

        // 初期ログイン状態を設定
        this.initLoginStatus();

        // 曜日ごとのボーナス表示を作成
        this.createWeekdayBonuses();

        // ログインボタンの作成
        this.createLoginButton();
      }

      // ログイン状態を更新するメソッド
      updateLoginStatus() {
        // 既存のボタンをクリア
        this.weekdayButtons.forEach(button => {
          if (button.cell) {
            button.cell.destroy();
          }
          if (button.check) {
            button.check.checkMark1.destroy();
            button.check.checkMark2.destroy();
          }
        });
        this.weekdayButtons = [];

        // ログイン状態を再初期化
        this.initLoginStatus();

        // ボーナス表示を再作成
        this.createWeekdayBonuses();

        // ログインボタンを再作成
        this.createLoginButton();
      }

      // 日付をYYYY-MM-DD形式に変換するヘルパー関数
      formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
      }

      // 曜日を日本語で取得するヘルパー関数
      getJapaneseWeekday(date) {
        const weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        return weekdays[date.getDay()];
      }

      // ログイン状態の初期化
      initLoginStatus() {
        // 今週の月曜日の日付を計算
        const today = this.currentDate;
        const dayOfWeek = today.getDay(); // 0:日曜, 1:月曜, ..., 6:土曜
        const mondayOffset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek; // 日曜なら前の月曜に、それ以外は今週の月曜に

        const monday = new Date(today);
        monday.setDate(today.getDate() + mondayOffset);

        // 月〜金のそれぞれの日付とログイン状態を設定
        for (let i = 0; i < 5; i++) {
          const date = new Date(monday);
          date.setDate(monday.getDate() + i);

          const dateStr = this.formatDate(date);
          // APIから取得したログイン状態を使用
          const bonusInfo = weeklyBonus.value.find(bonus => bonus.date === dateStr);
          const isLoggedIn = bonusInfo ? bonusInfo.isLoggedIn : false;

          this.loginStatus[dateStr] = {
            date: date,
            weekday: this.weekdays[i],
            reward: this.rewards[i],
            isLoggedIn: isLoggedIn,
            dateStr: dateStr
          };
        }

        // 現在の日付の文字列
        const currentDateStr = this.formatDate(today);
        currentLogin.value = currentDateStr;
      }

      // 曜日ごとのボーナス表示を作成
      createWeekdayBonuses() {
        const screenWidth = this.scale.width;
        const screenHeight = this.scale.height;
        const centerY = screenHeight / 2;
        const startX = screenWidth * 0.2;
        const spacing = screenWidth * 0.15;

        // 各曜日のボーナスボックスを作成
        Object.values(this.loginStatus).forEach((status, i) => {
          const x = startX + i * spacing;

          // 過去の日付かどうかを判定
          const isPast = status.date < this.currentDate &&
                        status.date.getDate() !== this.currentDate.getDate();

          // コインセル画像を表示
          const cellWidth = screenWidth * 0.18;
          const cellHeight = screenHeight * 0.28;
          const cell = this.add.image(x, centerY, 'box');
          cell.setDisplaySize(cellWidth, cellHeight);
          cell.setTint(isPast ? 0x666666 : 0xffffff);

          // 曜日
          this.add.text(x, centerY - cellHeight * 0.31, status.weekday + '曜日', {
            fontFamily: 'Arial',
            fontSize: Math.min(screenWidth * 0.03, 18),
            color: isPast ? '#666666' : '#333333'
          }).setOrigin(0.5);

          // 日付
          const dateText = `${status.date.getMonth() + 1}/${status.date.getDate()}`;
          this.add.text(x, centerY - cellHeight * 0.23, dateText, {
            fontFamily: 'Arial',
            fontSize: Math.min(screenWidth * 0.025, 16),
            color: isPast ? '#666666' : '#444444'
          }).setOrigin(0.5);

          // コイン画像を表示
          const coinSize = Math.min(screenWidth * 0.12, screenHeight * 0.2);
          const coin = this.add.image(x, centerY, 'coin');
          coin.setDisplaySize(coinSize, coinSize);
          coin.setTint(isPast ? 0x999999 : 0xffffff);

          // 報酬テキスト
          this.add.text(x, centerY + cellHeight * 0.28, `${status.reward}`, {
            fontFamily: 'Arial',
            fontSize: Math.min(screenWidth * 0.035, 28),
            color: isPast ? '#666666' : '#f0f0f0',
            stroke: '#000000',
            strokeThickness: 4,
            shadow: {
              offsetX: 2,
              offsetY: 2,
              color: '#000000',
              blur: 2,
              stroke: true,
              fill: true
            }
          }).setOrigin(0.5);

          // ログイン済みラベルを作成
          const labelText = this.add.text(x, centerY, '獲得済み', {
            fontFamily: 'Arial',
            fontSize: Math.min(screenWidth * 0.03, 18),
            color: '#ffffff',
            stroke: '#000000',
            strokeThickness: 4,
            shadow: {
              offsetX: 2,
              offsetY: 2,
              color: '#000000',
              blur: 2,
              stroke: true,
              fill: true
            }
          }).setOrigin(0.5).setDepth(300);

          // ラベルの背景を追加
          const labelBg = this.add.rectangle(x, centerY, labelText.width + 20, labelText.height + 20, 0x000000, 0.5)
            .setOrigin(0.5)
            .setDepth(200);

          // 初期状態ではラベルと背景は非表示
          labelText.setAlpha(0);
          labelBg.setAlpha(0);

          // APIから取得したログイン状態に基づいてラベルと背景を表示
          if (status.isLoggedIn) {
            labelText.setAlpha(1);
            labelBg.setAlpha(1);
          }

          // ボタンとラベルを配列に保存
          this.weekdayButtons.push({
            cell: cell,
            label: { text: labelText, bg: labelBg },
            status,
            x,
            y: centerY
          });
        });
      }

      // ログインボタンの作成
      createLoginButton() {
        const screenWidth = this.scale.width;
        const screenHeight = this.scale.height;
        const centerX = screenWidth / 2;
        const centerY = screenHeight / 2;
        const todayDateStr = this.formatDate(this.currentDate);
        const today = this.loginStatus[todayDateStr];

        // 今日が月〜金でない場合は表示しない
        if (!today) {
          this.add.text(centerX, centerY + screenHeight * 0.3, '今日はエントリーの対象外です', {
            fontFamily: 'Arial',
            fontSize: Math.min(screenWidth * 0.04, 22),
            color: '#ff8888'
          }).setOrigin(0.5);
          return;
        }

        // APIから取得したログイン状態に基づいてボタンを表示
        if (today.isLoggedIn) {
          this.add.text(centerX, centerY + screenHeight * 0.3, '今日はすでにエントリー済みです', {
            fontFamily: 'Arial',
            fontSize: Math.min(screenWidth * 0.04, 22),
            color: '#88ff88'
          }).setOrigin(0.5);
          return;
        }

        // エントリーボタン（画像の代わりに図形を使用）
        const buttonWidth = screenWidth * 0.25;
        const buttonHeight = screenHeight * 0.08;
        const buttonBg = this.add.rectangle(centerX, centerY + screenHeight * 0.3, buttonWidth, buttonHeight, 0x44aa44, 1).setStrokeStyle(2, 0x66ff66);
        buttonBg.setInteractive();

        const buttonText = this.add.text(centerX, centerY + screenHeight * 0.3, 'エントリー', {
          fontFamily: 'Arial',
          fontSize: Math.min(screenWidth * 0.04, 24),
          color: '#ffffff'
        }).setOrigin(0.5);

        // ボタンのホバー効果
        buttonBg.on('pointerover', () => {
          buttonBg.fillColor = 0x55cc55;
        });

        buttonBg.on('pointerout', () => {
          buttonBg.fillColor = 0x44aa44;
        });

        // ボタンクリックでエントリー処理
        buttonBg.on('pointerdown', () => {
          this.callLoginBonusAPI();
          buttonBg.disableInteractive();
          buttonBg.fillColor = 0x227722;
          buttonBg.setAlpha(0.7);

          // ローディング表示
          this.statusText.setText('エントリー処理中...');
        });

        this.loginButton = { bg: buttonBg, text: buttonText };
      }

      // ログインボーナスAPI呼び出し
      async callLoginBonusAPI() {
        try {
          // 現在の日付を取得
          const today = new Date();
          const dateStr = today.toISOString().split('T')[0];

          // デイリークエストを取得（空のクエストを除外）
          const dailyQuests = quests.value.filter(q => q.trim() !== '');

          // ワールドエントリーを作成
          const response = await axios.post('/api/world-entry', {
            date: dateStr,
            daily_quests: dailyQuests || []
          });

          if (response.data.success) {
            const reward = response.data.reward || 0;
            this.statusText.setText(`ワールド入場成功: ${reward}コインを獲得しました！`);

            // エフェクトの表示
            const centerX = this.cameras.main.width / 2;
            const centerY = this.cameras.main.height / 2;

            // パーティクルエミッターの作成（花火エフェクト）
            const emitter = this.add.particles(0, 0, 'coin', {
              x: centerX,
              y: centerY,
              speed: { min: 200, max: 400 },
              angle: { min: 0, max: 360 },
              scale: { start: 0.2, end: 0.0 },
              alpha: { start: 1, end: 0 },
              lifespan: 3000,
              quantity: 30,
              frequency: 50,
              gravityY: 100,
              blendMode: 'ADD',
              emitZone: {
                source: new Phaser.Geom.Circle(0, 0, 10),
                type: 'edge',
                quantity: 30
              }
            }).setDepth(1000);

            // 1秒後に放出を停止
            this.time.delayedCall(1000, () => {
              emitter.stop();
            });

            // 4秒後にエミッターを破棄
            this.time.delayedCall(4000, () => {
              emitter.destroy();
            });

            // コイン獲得アニメーション（最前面に表示）
            const coin = this.add.image(centerX, centerY, 'coin')
              .setScale(0.5)
              .setAlpha(0)
              .setDepth(1000); // 最前面に表示

            this.tweens.add({
              targets: coin,
              scale: 1,
              alpha: 1,
              duration: 500,
              ease: 'Back.easeOut',
              onComplete: () => {
                this.tweens.add({
                  targets: coin,
                  scale: 0.5,
                  alpha: 0,
                  duration: 500,
                  ease: 'Back.easeIn',
                  onComplete: () => {
                    coin.destroy();
                  }
                });
              }
            });

            // コイン獲得テキスト（最前面に表示）
            const coinText = this.add.text(centerX, centerY + 50, `+${reward}コイン`, {
              fontSize: '54px',
              fill: '#FFFFFF',
              stroke: '#000000',
              strokeThickness: 6,
              shadow: {
                offsetX: 2,
                offsetY: 2,
                color: '#000000',
                blur: 2,
                stroke: true,
                fill: true
              }
            }).setOrigin(0.5).setDepth(1000);

            this.tweens.add({
              targets: coinText,
              y: centerY - 50,
              alpha: 0,
              duration: 1500,
              ease: 'Power2',
              onComplete: () => {
                coinText.destroy();
              }
            });

            // ログインボーナス一覧を再取得
            fetchWeeklyBonus();
          } else {
            this.statusText.setText(`エラー: ${response.data.message}`);
            this.statusText.setTint(0xff0000);
          }
        } catch (error) {
          console.error('ワールド入場エラー:', error);
          this.statusText.setText('ワールド入場に失敗しました');
          this.statusText.setTint(0xff0000);
        }
      }

      // ワールドから退出
      exitWorld() {
        // ゲームを一時停止
        this.scene.pause();

        // コイン獲得数を計算
        const coinsEarned = Math.floor(this.coins / 10);

        // デイリークエストのリストを取得（空のクエストを除外）
        const dailyQuests = this.quests.filter(quest => quest.trim() !== '');

        // エントリーを更新
        axios.post('/api/world-entry', {
          coins_earned: coinsEarned,
          daily_quests: dailyQuests
        })
        .then(response => {
          // コインをリセット
          this.coins = 0;
          // ゲームを再開
          this.scene.resume();
        })
        .catch(error => {
          console.error('エントリーの更新に失敗しました:', error);
          // エラーが発生してもゲームを再開
          this.scene.resume();
        });
      }

      // リサイズ時の処理を追加
      resize() {
        if (this.backgroundImage) {
          const screenWidth = this.scale.width;
          this.backgroundImage.setScale(screenWidth / this.backgroundImage.width);
        }
      }

      update() {
        // 更新ロジック
      }
    }

    onMounted(() => {
      // nextTickを使用して、DOMの更新を待つ
      nextTick(() => {
        console.log('onMounted nextTick', gameContainer.value);
        // Phaserゲームの設定
        const config = {
          type: Phaser.AUTO,
          parent: gameContainer.value,
          width: window.innerWidth,
          height: window.innerHeight,
          scale: {
            mode: Phaser.Scale.RESIZE,
            autoCenter: Phaser.Scale.CENTER_BOTH,
            parent: gameContainer.value,
            width: '100%',
            height: '100%'
          },
          backgroundColor: '#000000',
          scene: LoginBonusScene
        };

        // ゲームインスタンスの作成
        game.value = new Phaser.Game(config);

        // ウィンドウのリサイズイベントを監視
        window.addEventListener('resize', handleResize);
      });
    });

    onUnmounted(() => {
      // コンポーネントのアンマウント時にゲームを破棄
      if (game.value) {
        game.value.destroy(true);
      }

      // イベントリスナーの削除
      window.removeEventListener('resize', handleResize);
    });

    // ウィンドウのリサイズイベントハンドラを修正
    const handleResize = () => {
      if (game.value) {
        game.value.scale.resize(window.innerWidth, window.innerHeight);
        // アクティブなシーンがあればリサイズ処理を実行
        const activeScene = game.value.scene.scenes.find(scene => scene.scene.isActive());
        if (activeScene && activeScene.resize) {
          activeScene.resize();
        }
      }
    };

    return {
      currentLogin,
      apiError,
      showDailyQuestModal,
      modalMessage,
      quests,
      weeklyBonus,
      addQuest,
      removeQuest,
      showModal,
      closeModal,
      handleWorldEntry
    };
  }
};
</script>

<style scoped>
.game-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    overflow: hidden;
}

.game-container {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: url('/assets/background.png') no-repeat center center;
    background-size: cover;
}

.game-canvas {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
    margin: 0;
    padding: 0;
}

.game-canvas canvas {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: block;
    margin: 0;
    padding: 0;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 2;
}

.modal-content {
    background: rgba(255, 255, 255, 0.9);
    padding: 2rem;
    border-radius: 1rem;
    max-width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    z-index: 3;
}

.game-canvas canvas {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

/* モーダルスタイル */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 100;
  overflow-y: auto;
  padding: 2rem;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 1rem;
  width: 80%;
  max-width: 600px;
  max-height: 90vh; /* 画面の高さの90%を最大値に */
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  position: relative;
  z-index: 101;
  display: flex;
  flex-direction: column;
  margin: auto;
}

.modal-content h2 {
  font-size: clamp(1.5rem, 2vw, 1.8rem); /* 画面サイズに応じてフォントサイズを調整 */
  font-weight: bold;
  margin-bottom: 1.5rem;
  color: #333;
  text-align: center;
  flex-shrink: 0;
}

.modal-content p {
  font-size: clamp(1rem, 1.5vw, 1.2rem); /* 画面サイズに応じてフォントサイズを調整 */
  margin-bottom: 2rem;
  color: #666;
  line-height: 1.6;
  text-align: center;
  flex-shrink: 0;
}

.modal-buttons {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: auto;
  flex-shrink: 0;
}

.modal-button {
  background: #4CAF50;
  color: white;
  padding: clamp(0.6rem, 1.5vw, 0.8rem) clamp(1.5rem, 3vw, 2rem);
  border: none;
  border-radius: 0.5rem;
  font-size: clamp(1rem, 1.5vw, 1.1rem);
  cursor: pointer;
  transition: all 0.3s ease;
  width: 100%;
  position: relative;
  box-shadow: 4px 4px 8px rgba(0, 0, 0, 0.2);
}

.modal-button:hover {
  background: #45a049;
  transform: translateY(-2px);
  box-shadow: 6px 6px 12px rgba(0, 0, 0, 0.3);
}

.modal-button:active {
  transform: translateY(0);
  box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.modal-button.add-quest {
  background: #2196F3;
  box-shadow: 4px 4px 8px rgba(33, 150, 243, 0.3);
}

.modal-button.add-quest:hover {
  background: #0b7dda;
  box-shadow: 6px 6px 12px rgba(33, 150, 243, 0.4);
}

.modal-button.add-quest:active {
  box-shadow: 2px 2px 4px rgba(33, 150, 243, 0.2);
}

.delete-button {
  background: #ff4444;
  color: white;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 2px 2px 4px rgba(255, 68, 68, 0.3);
}

.delete-button:hover {
  background: #cc0000;
  box-shadow: 3px 3px 6px rgba(255, 68, 68, 0.4);
}

.delete-button:active {
  box-shadow: 1px 1px 2px rgba(255, 68, 68, 0.2);
}

.quest-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.quest-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 0.5rem;
  border: 1px solid #ddd;
  width: 100%;
}

.quest-item:last-child {
  margin-bottom: 0;
}

.quest-checkbox {
  margin-right: 0.5rem;
  width: 1.2rem;
  height: 1.2rem;
  cursor: pointer;
}

.quest-text {
  flex: 1;
  margin: 0;
  font-size: 1rem;
  color: #333;
}

.quest-text.completed {
  text-decoration: line-through;
  color: #666;
}

.empty-quests {
  text-align: center;
  padding: 2rem;
  color: #8b4513;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 0.5rem;
  backdrop-filter: blur(5px);
}

.empty-quests p {
  margin: 0;
  font-family: 'Comic Sans MS', cursive, sans-serif;
}

.empty-quests-hint {
  font-size: 0.9rem;
  margin-top: 0.5rem;
  color: #a0522d;
}

.daily-quest-content {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 0.5rem;
  backdrop-filter: blur(5px);
}

.daily-quest-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.hero-image {
  width: 30px;
  height: 30px;
  object-fit: contain;
  filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.3));
}

.daily-quest-title {
  font-family: 'Comic Sans MS', cursive, sans-serif;
  font-size: 1.5rem;
  color: #8b4513;
  margin: 0;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
}

.quest-input {
  flex: 1;
  padding: 0.5rem;
  border: 2px solid #8b4513;
  border-radius: 0.5rem;
  font-size: 1rem;
  background: rgba(255, 255, 255, 0.9);
  color: #333;
  font-family: 'Comic Sans MS', cursive, sans-serif;
  width: 100%;
}

.quest-input:focus {
  outline: none;
  border-color: #a0522d;
  box-shadow: 0 0 5px rgba(139, 69, 19, 0.3);
}

.empty-quests {
  text-align: center;
  color: #666;
  font-style: italic;
  padding: 1rem;
}

.quest-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.quest-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 0.5rem;
  border: 1px solid #ddd;
}

.quest-item.completed {
  background: rgba(144, 238, 144, 0.3);
}

.quest-checkbox {
  width: 20px;
  height: 20px;
  cursor: pointer;
}

.quest-text {
  flex: 1;
  font-size: 1rem;
  color: #333;
}

.quest-text.completed {
  text-decoration: line-through;
  color: #666;
}

.delete-quest {
  background: none;
  border: none;
  color: #ff4444;
  cursor: pointer;
  font-size: 1.2rem;
  padding: 0.2rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.delete-quest:hover {
  color: #ff0000;
}

.btn.add-quest {
  background: #2196F3;
  box-shadow: 4px 4px 8px rgba(33, 150, 243, 0.3);
}

.btn.add-quest:hover {
  background: #0b7dda;
  box-shadow: 6px 6px 12px rgba(33, 150, 243, 0.4);
}

.btn.add-quest:active {
  box-shadow: 2px 2px 4px rgba(33, 150, 243, 0.2);
}
</style>