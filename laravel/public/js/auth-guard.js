// Cek token ada atau tidak, kalau tidak ada redirect ke login
function checkAuth(requiredRole) {
  const token = localStorage.getItem("token");
  const userStr = localStorage.getItem("user");

  if (!token || !userStr) {
    window.location.href = "login.html";
    return null;
  }

  const user = JSON.parse(userStr);

  if (requiredRole && user.role !== requiredRole) {
    alert("Anda tidak memiliki akses ke halaman ini");
    window.location.href = "login.html";
    return null;
  }

  return { token, user };
}

function logout() {
  localStorage.removeItem("token");
  localStorage.removeItem("user");
  window.location.href = "login.html";
}

async function apiFetch(url, options = {}) {
  const token = localStorage.getItem("token");
  const headers = {
    ...(options.headers || {}),
    Authorization: `Bearer ${token}`,
  };
  if (options.body && !(options.body instanceof FormData)) {
    headers["Content-Type"] = "application/json";
  }
  const response = await fetch(url, { ...options, headers });
  if (response.status === 401 || response.status === 403) {
    logout();
    throw new Error("Sesi habis, silakan login ulang");
  }
  return response;
}