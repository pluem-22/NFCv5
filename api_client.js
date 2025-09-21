// api_client.js - JavaScript client for NFC API
class NFCAPIClient {
    constructor(baseUrl = '') {
        this.baseUrl = baseUrl;
    }

    async makeRequest(endpoint, data) {
        try {
            const response = await fetch(this.baseUrl + endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            return {
                success: response.ok,
                data: result,
                status: response.status
            };
        } catch (error) {
            return {
                success: false,
                error: error.message,
                status: 0
            };
        }
    }

    // ลงทะเบียนบัตร
    async registerCard(uid, userId, nickname = '') {
        return await this.makeRequest('/api/card_register.php', {
            uid: uid,
            user_id: userId,
            nickname: nickname
        });
    }

    // ดูยอดคงเหลือ
    async getBalance(uid) {
        return await this.makeRequest('/api/get_balance.php', {
            uid: uid
        });
    }

    // เติมเงิน
    async topup(uid, amount) {
        return await this.makeRequest('/api/wallet_topup.php', {
            uid: uid,
            amount: amount
        });
    }

    // ซื้อสินค้า
    async buy(uid, amount) {
        return await this.makeRequest('/api/wallet_authorize.php', {
            uid: uid,
            amount: amount
        });
    }

    // ยืนยันธุรกรรม
    async confirm(uid, applied) {
        return await this.makeRequest('/api/wallet_confirm.php', {
            uid: uid,
            applied: applied
        });
    }
}

// ใช้งาน API Client
const nfcAPI = new NFCAPIClient();

// ฟังก์ชันสำหรับทดสอบ API
async function testNFCAPI() {
    console.log('Testing NFC API...');
    
    // ทดสอบดูยอดคงเหลือ
    const balanceResult = await nfcAPI.getBalance('04AABBCCDD');
    console.log('Balance result:', balanceResult);
    
    // ทดสอบเติมเงิน
    const topupResult = await nfcAPI.topup('04AABBCCDD', 100);
    console.log('Topup result:', topupResult);
    
    // ทดสอบซื้อสินค้า
    const buyResult = await nfcAPI.buy('04AABBCCDD', 50);
    console.log('Buy result:', buyResult);
}

// Export สำหรับใช้ในไฟล์อื่น
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { NFCAPIClient, nfcAPI };
}
