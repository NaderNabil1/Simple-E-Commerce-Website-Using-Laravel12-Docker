/* =================================================================
    Pie chart
================================================================= */

var ctx = document.getElementById("pie");

 var data = {
    labels: [
        "Apple",
        "Samsung",
        "LG"
    ],
    datasets: [{
        data: [250, 70, 160],
        backgroundColor: [
            "#3e70c9",
            "#f59345",
            "#f44236"
        ]
    }]
};

var myChart = new Chart(ctx, {
    type: 'pie',
    data: data
});

/* =================================================================
    Doughnut chart
================================================================= */

var ctx = document.getElementById("doughnut");

 var data = {
    labels: [
        "Apple",
        "Samsung",
        "LG"
    ],
    datasets: [{
        data: [250, 70, 160],
        backgroundColor: [
            "#3e70c9",
            "#f59345",
            "#f44236"
        ]
    }]
};

var myChart = new Chart(ctx, {
    type: 'doughnut',
    data: data
});

/* =================================================================
    Polar area chart
================================================================= */

var ctx = document.getElementById("polar-area");

 var data = {
    datasets: [{
        data: [
            11,
            25,
            17,
            8,
            30,
        ],
        backgroundColor: [
            "#f44236",
            "#43b968",
            "#f59345",
            "#777"
        ],
    }],
    labels: [
        "Red",
        "Green",
        "Orange",
        "Grey"
    ]
};

var myChart = new Chart(ctx, {
    type: 'polarArea',
    data: data,
});

/* =================================================================
   Radar chart
================================================================= */

var ctx = document.getElementById("radar");

var data = {
    labels: ["Eating", "Drinking", "Sleeping", "Designing", "Coding", "Cycling", "Running"],
    datasets: [
        {
            label: "2015",
            backgroundColor: "rgba(244,66,54,0.2)",
            borderColor: "#f44236",
            pointBackgroundColor: "#f44236",
            pointBorderColor: "#fff",
            pointHoverBackgroundColor: "#fff",
            pointHoverBorderColor: "#f44236",
            data: [65, 59, 90, 81, 56, 55, 40]
        },
        {
            label: "2016",
            backgroundColor: "rgba(153,153,153,0.2)",
            borderColor: "999",
            pointBackgroundColor: "999",
            pointBorderColor: "#fff",
            pointHoverBackgroundColor: "#fff",
            pointHoverBorderColor: "#999",
            data: [28, 48, 40, 19, 96, 27, 100]
        }
    ]
};

var myChart = new Chart(ctx, {
    type: 'radar',
    data: data,
});