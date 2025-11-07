<template>
  <div class="fixed bottom-6 right-6 z-50">
    <div v-if="open" class="w-80 bg-white shadow-xl rounded-lg overflow-hidden">
      <div class="flex items-center justify-between bg-blue-700 text-white px-4 py-2">
        <span class="font-bold">Live Chat</span>
        <button @click="open = false" class="text-xl">&times;</button>
      </div>
      <div class="h-64 overflow-y-auto p-4 space-y-2 bg-gray-50">
        <div v-for="msg in messages" :key="msg.id" :class="msg.user === user ? 'text-right' : 'text-left'">
          <div :class="msg.user === user ? 'bg-blue-100 ml-16' : 'bg-gray-200 mr-16'" class="inline-block px-3 py-2 rounded mb-1">
            <span class="text-xs text-gray-500">{{ msg.user }}</span><br>
            <span>{{ msg.text }}</span>
          </div>
        </div>
      </div>
      <form @submit.prevent="sendMessage" class="flex border-t">
        <input v-model="input" type="text" placeholder="Ketik pesan..." class="flex-1 px-3 py-2 outline-none">
        <button type="submit" class="bg-blue-700 text-white px-4">Kirim</button>
      </form>
    </div>
    <button v-else @click="open = true" class="bg-blue-700 text-white rounded-full shadow-lg w-16 h-16 flex items-center justify-center text-2xl">
      💬
    </button>
  </div>
</template>

<script>
export default {
  name: 'LiveChat',
  data() {
    return {
      open: false,
      input: '',
      user: window.Laravel?.user?.name || 'You',
      messages: [
        { id: 1, user: 'Admin', text: 'Selamat datang di Live Chat!' }
      ],
    };
  },
  methods: {
    sendMessage() {
      if (!this.input.trim()) return;
      this.messages.push({
        id: Date.now(),
        user: this.user,
        text: this.input
      });
      this.input = '';
      this.$nextTick(() => {
        const chatBox = this.$el.querySelector('.h-64');
        chatBox.scrollTop = chatBox.scrollHeight;
      });
    },
  },
};
</script>

<style scoped>
/* Custom styles if needed */
</style>
