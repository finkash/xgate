import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('reactionBar', ({ action, summary, currentReaction, token }) => ({
	action,
	token,
	summary,
	currentReaction,
	loading: false,
	icons: {
		like: '👍',
		love: '❤️',
		laugh: '😂',
		wow: '😮',
		sad: '😢',
		angry: '😡',
	},
	async toggle(type) {
		if (this.loading) {
			return;
		}

		this.loading = true;

		try {
			const response = await fetch(this.action, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					Accept: 'application/json',
					'X-CSRF-TOKEN': this.token,
				},
				body: JSON.stringify({ type }),
			});

			if (!response.ok) {
				return;
			}

			const data = await response.json();
			this.summary = data.counts ?? this.summary;
			this.currentReaction = data.current_user_reaction ?? null;
		} finally {
			this.loading = false;
		}
	},
	isActive(type) {
		return this.currentReaction === type;
	},
}));

Alpine.data('commentThread', ({ threadId, csrfToken }) => ({
	threadId,
	csrfToken,
	showAddComment: false,
	formError: '',
	loading: false,
	async submitForm(event) {
		const form = event.target;
		if (this.loading) {
			return;
		}

		this.loading = true;
		this.formError = '';

		try {
			const formData = new FormData(form);
			formData.delete('_redirect');

			const response = await fetch(form.action, {
				method: (form.method || 'POST').toUpperCase(),
				headers: {
					Accept: 'application/json',
					'X-CSRF-TOKEN': this.csrfToken,
				},
				body: formData,
			});

			if (!response.ok) {
				const errorPayload = await response.json().catch(() => null);
				if (errorPayload?.errors?.content?.length) {
					this.formError = errorPayload.errors.content[0];
				} else {
					this.formError = 'Could not submit comment. Please try again.';
				}
				return;
			}

			form.reset();
			this.showAddComment = false;
			await this.refreshThread();
		} catch {
			this.formError = 'Could not submit comment. Please try again.';
		} finally {
			this.loading = false;
		}
	},
	async refreshThread() {
		const pageResponse = await fetch(window.location.href, {
			headers: {
				Accept: 'text/html',
			},
		});

		if (!pageResponse.ok) {
			return;
		}

		const html = await pageResponse.text();
		const parser = new DOMParser();
		const doc = parser.parseFromString(html, 'text/html');
		const selector = `[data-comment-thread-id="${this.threadId}"]`;
		const incoming = doc.querySelector(selector);
		const current = document.querySelector(selector);

		if (!incoming || !current) {
			return;
		}

		current.innerHTML = incoming.innerHTML;
		Alpine.initTree(current);
	},
}));

Alpine.start();
