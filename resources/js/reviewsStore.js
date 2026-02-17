import { defineStore } from 'pinia'

export const useReviewsStore = defineStore('reviews', {

    state: () => ({
        allReviews: [],
    }),


    actions: {
        setAuthor() {
            this.allReviews.forEach((review) => {
                if (!review.author) {
                    review.author = 'Аноним'
                }
            })
        }
    },
})