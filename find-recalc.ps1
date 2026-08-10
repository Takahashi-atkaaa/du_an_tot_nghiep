$lines = Get-Content 'c:\laragon\www\du_an_tot_nghiep\public\js\admin\san-pham.js'
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match 'function recalcRow') {
        Write-Host "=== Line $($i+1) ==="
        for ($j = $i; $j -lt [Math]::Min($i + 80, $lines.Count); $j++) {
            Write-Host "$($j+1): $($lines[$j])"
        }
        break
    }
}
