<template>
  <div class="app-container">
    <h1>Vue 3 + Phaser 3 統合デモ</h1>
    <phaser-game></phaser-game>
    <hello-world></hello-world>
  </div>
</template>

<script>
import HelloWorld from './HelloWorld.vue'
import PhaserGame from './PhaserGame.vue'

export default {
    name: 'app',

    components: {
        HelloWorld,
        PhaserGame,
    },
}
</script>

<style lang="scss" scoped>
.app-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px;
}

h1 {
  margin-bottom: 20px;
  color: #333;
}
</style>