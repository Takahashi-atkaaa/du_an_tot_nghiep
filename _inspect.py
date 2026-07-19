import io
path = r'c:\laragon\www\du_an_tot_nghiep\resources\views\admin_xem_truoc\kiem_kho\create.blade.php'
with io.open(path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

# Tìm dòng bắt đầu <div class="flex flex-col lg:flex-row gap-6 items-start">
# và dòng kết thúc </div> đóng wrapper ngoài
start_idx = None
end_idx = None
for i, ln in enumerate(lines):
    if start_idx is None and 'class="flex flex-col lg:flex-row gap-6 items-start"' in ln:
        start_idx = i
    elif start_idx is not None and ln.strip() == '</div>' and i > start_idx + 50:
        # chỉ lấy </div> cuối cùng (đóng wrapper lớn)
        end_idx = i
        break

print(f'start_idx={start_idx}  end_idx={end_idx}')
if start_idx is not None and end_idx is not None:
    print('--- 5 dòng quanh start ---')
    for i in range(max(0, start_idx-2), min(len(lines), start_idx+3)):
        print(f'{i+1}: {lines[i]!r}')
    print('--- 5 dòng quanh end ---')
    for i in range(max(0, end_idx-2), min(len(lines), end_idx+3)):
        print(f'{i+1}: {lines[i]!r}')