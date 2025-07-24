class UidGenerator {
	static generateUid() {
		const uuid = UidGenerator.generateUuid();
		const timestamp = Date.now();
		const uid = `${uuid}-${timestamp}`;
		document.body.setAttribute('uid', uid);
		return uid;
	}

	static generateUuid() {
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
			const r = (Math.random() * 16) | 0;
			const v = c === 'x' ? r : (r & 0x3) | 0x8;
			return v.toString(16);
		});
	}
}
const uid = UidGenerator.generateUid();