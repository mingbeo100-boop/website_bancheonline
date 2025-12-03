<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo Thống Kê Quán Chè Theo Tháng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
      body {
        font-family: 'Inter', sans-serif;
        margin-top: 100px;

      }

      .chart-container {
        width: 100%;
        height: 450px;
        position: relative;
        /* Thêm padding để tránh biểu đồ bị cắt */
        padding-bottom: 20px; 
      }

      .styled-table {
        border-collapse: collapse;
        width: 100%;
        margin-top: 15px;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        border-radius: 0.5rem;
        overflow: hidden;
      }

      .styled-table thead tr {
        background-color: #4db6ac;
        color: white;
        text-align: left;
        text-transform: uppercase;
      }

      .styled-table th,
      .styled-table td {
        padding: 15px;
        text-align: left;
        border: 1px solid #e0f2f1;
      }

      .styled-table thead th:first-child {
        border-top-left-radius: 0.5rem;
      }

      .styled-table thead th:last-child {
        border-top-right-radius: 0.5rem;
      }

      .styled-table tbody tr:nth-child(even) {
        background-color: #f7f7f7;
      }
    </style>
  </head>
  <body class="bg-[#fce4ec] p-5 md:p-10 font-sans">
    <div class="container max-w-6xl mx-auto bg-white p-6 md:p-8 rounded-xl shadow-2xl">
      <h1 class="text-4xl font-extrabold text-[#880e4f] text-center mb-8" style="font-size: 70px;">
        <span class="text-[#ff8a65]" >🍨</span> Báo Cáo Thống Kê Quán Chè <span class="text-[#ff8a65]">🍵</span>
      </h1>
      <form id="filterForm" class="flex flex-col md:flex-row items-center justify-center gap-4 p-5 bg-[#e0f7fa] rounded-lg shadow-md mb-8" onsubmit="event.preventDefault(); fetchData();">
        <div class="flex items-center gap-2">
          <label for="month" class="font-semibold text-[#4a148c]">Chọn Tháng:</label>
          <select name="month" id="month" class="p-2 border border-[#b2dfdb] rounded-lg text-sm focus:ring-[#4db6ac] focus:border-[#4db6ac]">
            </select>
        </div>
        <div class="flex items-center gap-2">
          <label for="year" class="font-semibold text-[#4a148c]">Chọn Năm:</label>
          <select name="year" id="year" class="p-2 border border-[#b2dfdb] rounded-lg text-sm focus:ring-[#4db6ac] focus:border-[#4db6ac]">
            </select>
        </div>
        <button type="submit" class="bg-[#ff8a65] text-white font-bold py-2 px-4 rounded-lg hover:bg-[#ff5722] transition duration-300 shadow-xl">
          Xem Thống Kê
        </button>
      </form>
      <div id="dbErrorContainer" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative mb-4 shadow-lg" role="alert">
        <strong class="font-bold">LỖI HỆ THỐNG:</strong>
        <span id="dbErrorMessage" class="block sm:inline"></span>
      </div>
      <hr class="border-t-2 border-[#00acc1] my-8">
      <div id="loadingIndicator" class="text-center py-10 text-xl text-[#880e4f] hidden">Đang tải dữ liệu...</div>
      <h2 class="text-2xl font-bold text-[#00897b] border-b-2 border-[#00acc1] pb-2 mt-10" id="revenueTitle">
        💰 Thống Kê Doanh Thu (<span id="currentMonthYear1">...</span>)
      </h2>
      <div class="chart-container mb-8">
        <canvas id="doanhThuChart"></canvas>
        <div id="noDataChart" class="p-4 bg-gray-100 rounded-xl text-center text-gray-500 h-full flex items-center justify-center absolute top-0 left-0 right-0 bottom-0 hidden">
          <p class="text-lg font-medium">Không có dữ liệu doanh thu hợp lệ để vẽ biểu đồ cho tháng này.</p>
        </div>
      </div>
      <table class="styled-table">
        <thead>
          <tr>
            <th class="rounded-tl-lg">Ngày</th>
            <th>Tổng Doanh Thu (VNĐ)</th>
            <th>Số Lượng Đơn Hàng</th>
          </tr>
        </thead>
        <tbody id="dailySalesBody"></tbody>
      </table>
      <h2 class="text-2xl font-bold text-[#00897b] border-b-2 border-[#00acc1] pb-2 mt-10" id="customerTitle">
        👥 Thống Kê Khách Hàng (<span id="currentMonthYear2">...</span>)
      </h2>
      <table class="styled-table mb-10">
        <thead>
          <tr>
            <th class="rounded-tl-lg">Loại Khách Hàng</th>
            <th>Số Lượng</th>
            <th>Phần Trăm</th>
          </tr>
        </thead>
        <tbody id="customerStatsBody">
          </tbody>
      </table>
    </div>
    <script>
      let myChart = null;
      // Đổi thành tên file PHP backend của bạn nếu khác
      const apiEndpoint = '../backend/api.php'; 
      const currentYear = new Date().getFullYear();
      // Hàm định dạng số thành VNĐ (có dấu phẩy)
      const formatCurrency = (number) => {
        // Làm tròn số và định dạng
        return new Intl.NumberFormat('vi-VN').format(Math.round(number));
      };
      
      // Hàm tạo options cho select box
      const populateSelect = (elementId, start, end, selected) => {
        const select = document.getElementById(elementId);
        select.innerHTML = '';
        for (let i = start; i <= end; i++) {
          const value = String(i).padStart(2, '0');
          const option = document.createElement('option');
          option.value = value;
          option.textContent = i;
          if (value === String(selected).padStart(2, '0')) {
            option.selected = true;
          }
          select.appendChild(option);
        }
      };

      // Hàm khởi tạo bộ lọc
      const initializeFilters = () => {
        const today = new Date();
        // Lấy tháng và năm hiện tại, và chuẩn hóa thành chuỗi 2 ký tự (vd: '01', '12')
        const defaultMonth = String(today.getMonth() + 1).padStart(2, '0');
        const defaultYear = String(today.getFullYear());

        // Lọc 5 năm (Năm hiện tại và 4 năm trước)
        populateSelect('month', 1, 12, defaultMonth);
        populateSelect('year', currentYear - 4, currentYear, defaultYear);

        // Cập nhật giá trị nếu có tham số trên URL (để giữ trạng thái sau khi lọc)
        const urlParams = new URLSearchParams(window.location.search);
        const monthParam = urlParams.get('month');
        const yearParam = urlParams.get('year');

        if (monthParam) document.getElementById('month').value = String(monthParam).padStart(2, '0');
        if (yearParam) document.getElementById('year').value = yearParam;
      };

      // Hàm vẽ biểu đồ
      const drawChart = (labels, dataValues) => {
        const canvas = document.getElementById('doanhThuChart');
        const noData = document.getElementById('noDataChart');

        // Xóa biểu đồ cũ nếu tồn tại
        if (myChart) {
          myChart.destroy();
        }

        if (dataValues.every(val => val === 0) || dataValues.length === 0) {
          canvas.style.display = 'none';
          noData.classList.remove('hidden');
          return;
        }

        canvas.style.display = 'block';
        noData.classList.add('hidden');

        const data = {
          labels: labels,
          datasets: [{
            label: 'Doanh Thu (VNĐ)',
            data: dataValues,
            backgroundColor: ['rgba(77, 182, 172, 0.8)'],
            borderColor: ['#00897b'],
            borderWidth: 2,
            borderRadius: 5,
          }]
        };

        const config = {
          type: 'bar',
          data: data,
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'top'
              },
              title: {
                display: true,
                text: 'Biểu Đồ Doanh Thu Theo Ngày',
                font: {
                  size: 16,
                  weight: 'bold'
                },
                color: '#333'
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                title: {
                  display: true,
                  text: 'Doanh Thu (VNĐ)',
                  color: '#555'
                },
                ticks: {
                  // Định dạng hiển thị trên trục Y
                  callback: function(value) {
                    if (value >= 1000000) {
                      return formatCurrency(value / 1000000) + ' Tr VNĐ';
                    }
                    if (value >= 1000) {
                      return formatCurrency(value / 1000) + ' K';
                    }
                    return formatCurrency(value);
                  }
                }
              },
              x: {
                grid: {
                  display: false
                }
              }
            }
          }
        };

        myChart = new Chart(canvas, config);
      };

      // Hàm hiển thị dữ liệu lên bảng
      const displayData = (data) => {
        const monthYear = `Tháng ${data.selected_month}/${data.selected_year}`;
        document.getElementById('currentMonthYear1').textContent = monthYear;
        document.getElementById('currentMonthYear2').textContent = monthYear;

        // 1. Bảng Doanh thu
        const dailySalesBody = document.getElementById('dailySalesBody');
        let salesHtml = '';

        if (data.daily_sales.length > 0) {
          data.daily_sales.forEach(row => {
            salesHtml += `
              <tr>
                <td>${row.day}/${data.selected_month}</td>
                <td>${formatCurrency(row.revenue)} VNĐ</td>
                <td>${row.orders}</td>
              </tr>
            `;
          });

          // Hàng tổng cộng
          salesHtml += `
            <tr class="bg-[#fff3e0] font-bold text-[#ff5722]">
              <td class="font-bold">TỔNG CỘNG</td>
              <td class="font-bold">${formatCurrency(data.totals.revenue)} VNĐ</td>
              <td class="font-bold">${data.totals.orders}</td>
            </tr>
          `;

          // Chuẩn bị dữ liệu cho biểu đồ
          const labels = data.daily_sales.map(item => `Ngày ${item.day}`);
          const dataValues = data.daily_sales.map(item => item.revenue);
          drawChart(labels, dataValues);
        } else {
          salesHtml = `
            <tr>
              <td colspan='3' class='text-center text-gray-500'>Không có dữ liệu doanh thu cho tháng này.</td>
            </tr>`;
          drawChart([], []); // Vẽ biểu đồ rỗng
        }
        dailySalesBody.innerHTML = salesHtml;

        // 2. Bảng Khách hàng
        const customerStatsBody = document.getElementById('customerStatsBody');
        let customerHtml = '';

        if (data.customer_stats.length > 0 || data.totals.customers > 0) {
          data.customer_stats.forEach(row => {
            customerHtml += `
              <tr>
                <td>${row.type}</td>
                <td>${row.count}</td>
                <td>${row.percent}</td>
              </tr>
            `;
          });

          // Hàng tổng cộng
          customerHtml += `
            <tr class="bg-[#e1f5fe] font-bold text-[#0277bd]">
              <td class="font-bold">TỔNG KHÁCH HÀNG</td>
              <td class="font-bold">${data.totals.customers}</td>
              <td class="font-bold">100%</td>
            </tr>
          `;
        } else {
          customerHtml = `
            <tr>
              <td colspan='3' class='text-center text-gray-500'>Không có dữ liệu khách hàng cho tháng này.</td>
            </tr>`;
        }
        customerStatsBody.innerHTML = customerHtml;
      };

      // Hàm gọi API để lấy dữ liệu
      const fetchData = async () => {
        document.getElementById('loadingIndicator').classList.remove('hidden');
        document.getElementById('dbErrorContainer').classList.add('hidden');

        const month = document.getElementById('month').value;
        const year = document.getElementById('year').value;

        // Cập nhật URL để giữ trạng thái lọc
        const newUrl = `${window.location.pathname}?month=${month}&year=${year}`;
        window.history.pushState({
          path: newUrl
        }, '', newUrl);

        try {
          const response = await fetch(`${apiEndpoint}?month=${month}&year=${year}`);
          const result = await response.json();

          if (result.error) {
            // Xử lý lỗi CSDL hoặc lỗi server
            document.getElementById('dbErrorMessage').textContent = result.error;
            document.getElementById('dbErrorContainer').classList.remove('hidden');
            
            // Xóa dữ liệu cũ và hiển thị lỗi
            drawChart([], []); 
            document.getElementById('dailySalesBody').innerHTML = `<tr><td colspan='3' class='text-center text-red-500'>Không thể tải dữ liệu: Lỗi CSDL.</td></tr>`;
            document.getElementById('customerStatsBody').innerHTML = `<tr><td colspan='3' class='text-center text-red-500'>Không thể tải dữ liệu: Lỗi CSDL.</td></tr>`;
          } else {
            // Hiển thị dữ liệu thành công
            displayData(result.data);
          }
        } catch (error) {
          // Xử lý lỗi mạng hoặc lỗi parsing JSON
          document.getElementById('dbErrorMessage').textContent = `Lỗi kết nối hoặc xử lý dữ liệu: ${error.message}. Vui lòng kiểm tra file ${apiEndpoint} và kết nối mạng.`;
          document.getElementById('dbErrorContainer').classList.remove('hidden');
          
          drawChart([], []);
          document.getElementById('dailySalesBody').innerHTML = `<tr><td colspan='3' class='text-center text-red-500'>Lỗi kết nối API.</td></tr>`;
          document.getElementById('customerStatsBody').innerHTML = `<tr><td colspan='3' class='text-center text-red-500'>Lỗi kết nối API.</td></tr>`;
        } finally {
          document.getElementById('loadingIndicator').classList.add('hidden');
        }
      };

      // Chạy khi trang tải xong
      window.onload = () => {
        initializeFilters();
        fetchData();
      };
    </script>
  </body>
</html>