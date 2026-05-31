document.addEventListener("DOMContentLoaded", function () {
    const root = document.documentElement;
    if (root.dataset.tacticumChartsInit === "true") return;
    root.dataset.tacticumChartsInit = "true";

    const target = document.getElementById("package-comparison-chart");
    if (!target || !window.echarts) return;

    const packageChart = window.echarts.init(target);
    packageChart.setOption({
        animation: false,
        tooltip: {
            trigger: "axis",
            backgroundColor: "rgba(255, 255, 255, 0.9)",
            borderColor: "#e2e8f0",
            borderWidth: 1,
            textStyle: { color: "#1f2937" },
        },
        legend: {
            data: ["Стартовый", "Бизнес", "Корпоративный"],
            bottom: 10,
            textStyle: { color: "#1f2937" },
        },
        grid: { left: "3%", right: "4%", bottom: "15%", top: "10%", containLabel: true },
        xAxis: {
            type: "category",
            data: ["Скорость запуска", "Экономия", "Количество специалистов", "Поддержка", "Гибкость"],
            axisLine: { lineStyle: { color: "#e2e8f0" } },
            axisLabel: { color: "#1f2937" },
        },
        yAxis: {
            type: "value",
            axisLine: { lineStyle: { color: "#e2e8f0" } },
            axisLabel: { color: "#1f2937" },
            splitLine: { lineStyle: { color: "#f1f5f9" } },
        },
        series: [
            {
                name: "Стартовый",
                type: "bar",
                data: [90, 15, 3, 60, 75],
                itemStyle: { color: "rgba(87, 181, 231, 1)" },
                barWidth: "20%",
                barGap: "10%",
                barCategoryGap: "30%",
                emphasis: { itemStyle: { color: "rgba(87, 181, 231, 0.8)" } },
            },
            {
                name: "Бизнес",
                type: "bar",
                data: [70, 20, 5, 80, 85],
                itemStyle: { color: "rgba(141, 211, 199, 1)" },
                barWidth: "20%",
                emphasis: { itemStyle: { color: "rgba(141, 211, 199, 0.8)" } },
            },
            {
                name: "Корпоративный",
                type: "bar",
                data: [50, 25, 6, 95, 90],
                itemStyle: { color: "rgba(251, 191, 114, 1)" },
                barWidth: "20%",
                emphasis: { itemStyle: { color: "rgba(251, 191, 114, 0.8)" } },
            },
        ],
    });

    window.addEventListener("resize", function () {
        packageChart.resize();
    });
});
