<?php
// $notifications: mảng từ API
// $filters: ['page','limit','type','isRead','patientId']
$unread = array_values(array_filter($notifications ?? [], fn($n) => empty($n['isRead'])));
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<link rel="stylesheet" href="/css/report.css">
<div class="container" style="padding:16px">
  <h2>Thông báo</h2>

  <form method="get" action="/notifications/index" class="row g-2" style="margin:12px 0">
    <input type="hidden" name="patientId" value="<?= h($filters['patientId'] ?? '') ?>">
    <div class="col-auto">
      <label class="form-label">Loại</label>
      <select name="type" class="form-select">
        <option value="">Tất cả</option>
        <option value="appointment" <?= ($filters['type']??'')==='appointment'?'selected':'' ?>>Lịch khám</option>
        <option value="prescription" <?= ($filters['type']??'')==='prescription'?'selected':'' ?>>Đơn thuốc</option>
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label">Trạng thái</label>
      <select name="isRead" class="form-select">
        <option value="">Tất cả</option>
        <option value="false" <?= ($filters['isRead']??'')==='false'?'selected':'' ?>>Chưa đọc</option>
        <option value="true"  <?= ($filters['isRead']??'')==='true'?'selected':'' ?>>Đã đọc</option>
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label">Trang</label>
      <input type="number" name="page" min="1" class="form-control" value="<?= (int)($filters['page']??1) ?>">
    </div>
    <div class="col-auto">
      <label class="form-label">Mỗi trang</label>
      <input type="number" name="limit" min="1" max="100" class="form-control" value="<?= (int)($filters['limit']??20) ?>">
    </div>
    <div class="col-auto" style="padding-top:30px">
      <button class="btn btn-primary">Lọc</button>
      <a class="btn btn-outline-secondary" href="/notifications/index">Reset</a>
    </div>
  </form>

  <div style="margin:10px 0">
    <form method="post" action="/notifications/readAll" style="display:inline">
      <input type="hidden" name="patientId" value="<?= h($filters['patientId'] ?? '') ?>">
      <button class="btn btn-success">Đánh dấu tất cả đã đọc (<?= count($unread) ?> chưa đọc)</button>
    </form>
  </div>

  <?php if (empty($notifications)): ?>
    <div class="alert alert-info">Không có thông báo.</div>
  <?php else: ?>
    <ul class="list-group">
      <?php foreach ($notifications as $n): ?>
        <li class="list-group-item" style="<?= empty($n['isRead']) ? 'background:#fff8e1' : '' ?>">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <div>
              <div><b><?= h($n['type'] ?? '') ?></b>
                <?php if (!empty($n['event'])): ?> — <i><?= h($n['event']) ?></i><?php endif; ?>
              </div>
              <div><?= h($n['message'] ?? '') ?></div>
              <div style="font-size:12px;color:#666">
                <?= h($n['createdAt'] ?? $n['created_at'] ?? '') ?>
              </div>
            </div>

            <?php if (empty($n['isRead'])): ?>
              <form method="post" action="/notifications/read">
                <input type="hidden" name="id" value="<?= h($n['_id'] ?? $n['id'] ?? '') ?>">
                <button class="btn btn-sm btn-outline-secondary">Đánh dấu đã đọc</button>
              </form>
            <?php else: ?>
              <span class="badge bg-secondary">Đã đọc</span>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
