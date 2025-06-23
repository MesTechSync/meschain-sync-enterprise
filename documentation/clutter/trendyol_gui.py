import tkinter as tk
from tkinter import messagebox, scrolledtext
import requests
import base64

# Trendyol API fonksiyonu
def trendyol_api_test(email, password, supplier_id):
    try:
        credentials = f"{email}:{password}"
        token = base64.b64encode(credentials.encode()).decode()
        headers = {
            "Authorization": f"Basic {token}",
            "Content-Type": "application/json"
        }
        url = f"https://api.trendyol.com/sapigw/suppliers/{supplier_id}/orders"
        params = {"status": "Created", "size": 5}
        response = requests.get(url, headers=headers, params=params)
        if response.status_code == 200:
            data = response.json()
            return True, data
        else:
            return False, f"Kod: {response.status_code}\nHata: {response.text}"
    except Exception as e:
        return False, str(e)

# Giriş ve API test GUI'si
def main():
    def giris_yap():
        email = entry_email.get()
        password = entry_password.get()
        supplier_id = entry_supplier.get()
        if not email or not password or not supplier_id:
            messagebox.showerror("Hata", "Lütfen tüm alanları doldurun!")
            return
        btn_giris.config(state=tk.DISABLED)
        text_sonuc.delete(1.0, tk.END)
        text_sonuc.insert(tk.END, "API'ye bağlanılıyor...\n")
        root.update()
        basarili, sonuc = trendyol_api_test(email, password, supplier_id)
        if basarili:
            text_sonuc.insert(tk.END, "\n✅ Trendyol API bağlantısı başarılı!\n\n")
            text_sonuc.insert(tk.END, f"Sipariş verileri:\n{sonuc}\n")
        else:
            text_sonuc.insert(tk.END, f"\n❌ Bağlantı başarısız!\n{sonuc}\n")
        btn_giris.config(state=tk.NORMAL)

    root = tk.Tk()
    root.title("Trendyol API Giriş ve Test Paneli")
    root.geometry("500x400")
    root.resizable(False, False)

    tk.Label(root, text="E-posta (Kullanıcı Adı):").pack(pady=(15,0))
    entry_email = tk.Entry(root, width=40)
    entry_email.pack()
    entry_email.insert(0, "mustafahelvaci.tr@gmail.com")

    tk.Label(root, text="Şifre:").pack(pady=(10,0))
    entry_password = tk.Entry(root, show="*", width=40)
    entry_password.pack()
    entry_password.insert(0, "Y7f6uv#$")

    tk.Label(root, text="Supplier ID:").pack(pady=(10,0))
    entry_supplier = tk.Entry(root, width=40)
    entry_supplier.pack()
    entry_supplier.insert(0, "1076956")

    btn_giris = tk.Button(root, text="Giriş Yap ve Trendyol API Testi", command=giris_yap)
    btn_giris.pack(pady=15)

    text_sonuc = scrolledtext.ScrolledText(root, width=60, height=10)
    text_sonuc.pack(padx=10, pady=10)

    root.mainloop()

if __name__ == "__main__":
    main() 