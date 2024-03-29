const CONFIG = {
    PORT: process.env.PORT || 3000,
    //CHAT_NAMESPACE: '/video-chat',
    REDIS_HOST: process.env.REDIS_HOST || 'redis',
    REDIS_PORT: process.env.REDIS_PORT || 6379,
    ORIGINS: process.env.ORIGINS || '*:*',
    KEY: 'unique'
}

module.exports = CONFIG
