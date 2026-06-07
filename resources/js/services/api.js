// API service for communicating with Laravel backend
const API_BASE_URL = '/api';

// Helper to get token from localStorage
const getToken = () => localStorage.getItem('token') || localStorage.getItem('auth_token');

// Helper to make authenticated requests
const fetchWithAuth = async (url, options = {}) => {
  const token = getToken();
  
  const headers = {
    'Accept': 'application/json',
    ...options.headers,
  };

  // Si el body NO es un FormData (archivos), le decimos que es JSON
  if (!(options.body instanceof FormData)) {
    headers['Content-Type'] = 'application/json';
  }

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const response = await fetch(`${API_BASE_URL}${url}`, {
    ...options,
    headers,
  });

  return response;
};

export const api = {
  // ===== Auth =====
  async login(email, password) {
    console.log("Datos recibidos para login:", email, password);
    try {
      const response = await fetchWithAuth('/login', {
        method: 'POST',
        body: JSON.stringify({ email, password }),
      });
      const data = await response.json();
      
      if (data.success && data.token) {
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
      }
      
      return data;
    } catch (error) {
      console.error('Error en login:', error);
      return { success: false, message: 'Error en login' };
    }
  },

  async register(name, email, password, passwordConfirmation) {
    try {
      const response = await fetchWithAuth('/register', {
        method: 'POST',
        body: JSON.stringify({
          name,
          email,
          password,
          password_confirmation: passwordConfirmation,
        }),
      });
      const data = await response.json();

      if (data.success && data.token) {
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
      }

      return data;
    } catch (error) {
      console.error('Error en registro:', error);
      return { success: false, message: 'Error en registro' };
    }
  },
  async verifyCode(email, code) {
    try {
      const response = await fetchWithAuth('/verify-code', {
        method: 'POST',
        body: JSON.stringify({ email, code }),
      });
      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Error en verificación:', error);
      return { success: false, message: 'Fallo de conexión al verificar el código' };
    }
  },

  async logout() {
    try {
      await fetchWithAuth('/logout', { method: 'POST' });
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      return { success: true };
    } catch (error) {
      console.error('Error en logout:', error);
      return { success: false };
    }
  },

  async getMe() {
    try {
      const response = await fetchWithAuth('/me');
      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Error obteniendo usuario:', error);
      return { success: false };
    }
  },

  getCurrentUser() {
    const userStr = localStorage.getItem('user');
    return userStr ? JSON.parse(userStr) : null;
  },

  isAuthenticated() {
    return !!getToken();
  },

  // ===== Products =====
  async getProducts() {
    try {
      const response = await fetch(`${API_BASE_URL}/products`);
      const data = await response.json();
      return data.data || [];
    } catch (error) {
      console.error('Error obteniendo productos:', error);
      return [];
    }
  },

  async getProduct(id) {
    try {
      const response = await fetch(`${API_BASE_URL}/products/${id}`);
      const data = await response.json();
      return data.data;
    } catch (error) {
      console.error('Error obteniendo producto:', error);
      return null;
    }
  },

  // ===== Admin Products =====
  async getAdminProducts() {
    try {
      const response = await fetchWithAuth('/admin/products');
      const data = await response.json();
      return data.data || [];
    } catch (error) {
      console.error('Error obteniendo productos admin:', error);
      return [];
    }
  },

  async createProduct(productData) {
    try {
      const formData = new FormData();
      
      for (const key in productData) {
        if (productData[key] !== null && productData[key] !== undefined) {
          let finalValue = productData[key];
          if (typeof finalValue === 'boolean') {
            finalValue = finalValue ? 1 : 0;
          }
          formData.append(key, finalValue);
        }
      }

      const response = await fetchWithAuth('/admin/products', {
        method: 'POST',
        body: formData,
      });
      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Error creando producto:', error);
      return { success: false, message: 'Error creando producto' };
    }
  },

  async updateProduct(id, productData) {
    try {
      const formData = new FormData();
      
      formData.append('_method', 'PUT');

      for (const key in productData) {
        if (productData[key] !== null && productData[key] !== undefined) {
          if (key === 'image' && !(productData[key] instanceof File)) {
            continue; 
          }
          
          let finalValue = productData[key];
          if (typeof finalValue === 'boolean') {
            finalValue = finalValue ? 1 : 0;
          }
          formData.append(key, finalValue);
        }
      }

      const response = await fetchWithAuth(`/admin/products/${id}`, {
        method: 'POST',
        body: formData,
      });
      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Error actualizando producto:', error);
      return { success: false, message: 'Error actualizando producto' };
    }
  },

  async deleteProduct(id) {
    try {
      const response = await fetchWithAuth(`/admin/products/${id}`, {
        method: 'DELETE',
      });
      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Error eliminando producto:', error);
      return { success: false, message: 'Error eliminando producto' };
    }
  },

  // ===== Categories =====
  async getCategories() {
    try {
      const response = await fetch(`${API_BASE_URL}/categories`);
      const data = await response.json();
      return data.data || [];
    } catch (error) {
      console.error('Error obteniendo categorías:', error);
      return [];
    }
  },

  async getCategory(id) {
    try {
      const response = await fetch(`${API_BASE_URL}/categories/${id}`);
      const data = await response.json();
      return data.data;
    } catch (error) {
      console.error('Error obteniendo categoría:', error);
      return null;
    }
  },

  // ===== Payments (MercadoPago Checkout Pro) =====
  
  async createCheckoutPreference(orderData) {
    try {
      const response = await fetchWithAuth('/payment/preference', {
        method: 'POST',
        body: JSON.stringify(orderData),
      });
      return await response.json();
    } catch (error) {
      console.error('Error creando preferencia de pago:', error);
      return { success: false, error: 'No se pudo generar el link de pago' };
    }
  },
  // Obtener historial de pedidos
  async getMyOrders() {
    try {
      const token = localStorage.getItem('token');
      const response = await fetch('https://funko.blog/api/my-orders', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });
      return await response.json();
    } catch (error) {
      console.error('Error obteniendo pedidos:', error);
      return { success: false, orders: [] };
    }
  },
  // Traer todas las órdenes (Solo Admin)
  async getAllOrders() {
    try {
      const token = localStorage.getItem('token');
      const response = await fetch('/api/admin/orders', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });
      return await response.json();
    } catch (error) {
      console.error('Error obteniendo todas las órdenes:', error);
      return { success: false, orders: [] };
    }
  },

  // Cambiar el estado de una orden (Solo Admin)
  async updateOrderStatus(orderId, status) {
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(`/api/admin/orders/${orderId}/status`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ status })
      });
      return await response.json();
    } catch (error) {
      console.error('Error actualizando estado:', error);
      return { success: false };
    }
  },
};