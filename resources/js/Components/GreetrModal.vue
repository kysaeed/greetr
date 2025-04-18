<template>
    <div v-if="show" class="modal-overlay" @click="closeOnOverlay && $emit('close')">
        <div class="modal-carto" @click.stop>
            <div class="modal-header">
                <slot name="title">
                    <h2 class="modal-title">{{ title }}</h2>
                </slot>
            </div>
            <div class="modal-body">
                <slot></slot>
            </div>
            <div class="modal-footer">
                <slot name="footer">
                    <button class="btn" @click="$emit('close')">閉じる</button>
                </slot>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    show: {
        type: Boolean,
        required: true
    },
    title: {
        type: String,
        default: ''
    },
    closeOnOverlay: {
        type: Boolean,
        default: true
    }
});

defineEmits(['close']);
</script>

<style scoped>
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

.modal-carto {
  background: white;
  padding: 2rem;
  border-radius: 1rem;
  width: 80%;
  max-width: 600px;
  max-height: 90vh;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  position: relative;
  z-index: 101;
  display: flex;
  flex-direction: column;
  margin: auto;
  overflow: hidden;
}

.modal-carto::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: 1rem;
  z-index: -1;
}

.modal-header {
  margin-bottom: 1.5rem;
}

.modal-title {
  font-size: clamp(1.5rem, 2vw, 1.8rem);
  font-weight: bold;
  color: #333;
  text-align: center;
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  margin-bottom: 1.5rem;
  padding-right: 0.5rem;
}

.modal-body::-webkit-scrollbar {
  width: 8px;
}

.modal-body::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: #8b4513;
  border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: #a0522d;
}

.modal-footer {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: auto;
}

.btn {
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

.btn:hover {
  background: #45a049;
  transform: translateY(-2px);
  box-shadow: 6px 6px 12px rgba(0, 0, 0, 0.3);
}

.btn:active {
  transform: translateY(0);
  box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
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

export default {
  name: 'GreetrModal',
}